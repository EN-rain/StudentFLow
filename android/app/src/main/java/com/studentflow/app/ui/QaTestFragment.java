package com.studentflow.app.ui;

import com.google.android.material.button.MaterialButton;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import com.studentflow.app.api.ApiClient;
import com.studentflow.app.data.TokenStore;

import java.util.ArrayList;
import java.util.List;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class QaTestFragment extends BaseDataFragment {
    private final List<QaStep> steps = new ArrayList<>();
    private MaterialButton runButton;
    private int passed;
    private int failed;
    private int currentClassId;
    private int currentStudentId;
    private int currentExamId;
    private String role = "";

    public static QaTestFragment newInstance() {
        return new QaTestFragment();
    }

    @Override
    protected void configure() {
        setHeader("QA Tests", "Run automated API smoke tests while the phone stays open.");
        runButton = addAction("Run All Tests", v -> runAll());
        addCard("Ready. Keep the app open and tap Run All Tests.");
    }

    private void runAll() {
        runButton.setEnabled(false);
        passed = 0;
        failed = 0;
        currentClassId = 0;
        currentStudentId = 0;
        currentExamId = 0;
        role = readRole();
        steps.clear();
        listContainer.removeAllViews();
        statusView.setText("Running QA tests...");
        buildPlan();
        runStep(0);
    }

    private void buildPlan() {
        steps.add(new QaStep("Auth profile", () -> ApiClient.service(requireContext()).me()));

        if ("student".equals(role)) {
            steps.add(new QaStep("Student dashboard", () -> ApiClient.service(requireContext()).studentDashboard()));
            steps.add(new QaStep("Student profile", () -> ApiClient.service(requireContext()).studentProfile()));
            steps.add(new QaStep("Student classes", () -> ApiClient.service(requireContext()).studentClasses()));
            steps.add(new QaStep("Student announcements", () -> ApiClient.service(requireContext()).studentAnnouncements()));
            steps.add(new QaStep("Student assignments", () -> ApiClient.service(requireContext()).studentAssignments()));
            steps.add(new QaStep("Student grades", () -> ApiClient.service(requireContext()).studentGrades()));
            steps.add(new QaStep("Student attendance", () -> ApiClient.service(requireContext()).studentAttendance()));
            steps.add(new QaStep("Student exams", () -> ApiClient.service(requireContext()).studentExams()));
            return;
        }

        if ("admin".equals(role)) {
            steps.add(new QaStep("Admin teachers", () -> ApiClient.service(requireContext()).adminTeachers()));
            steps.add(new QaStep("Admin settings", () -> ApiClient.service(requireContext()).adminSettings()));
            steps.add(new QaStep("Admin activity logs", () -> ApiClient.service(requireContext()).adminActivityLogs()));
        }

        steps.add(new QaStep("Classes", () -> ApiClient.service(requireContext()).classes(), body -> {
            JsonObject first = firstDataObject(body);
            currentClassId = idFrom(first);
        }));
        steps.add(new QaStep("Students", () -> ApiClient.service(requireContext()).students(null, currentClassId == 0 ? null : currentClassId), body -> {
            JsonObject first = firstDataObject(body);
            currentStudentId = idFrom(first);
        }));
        steps.add(new QaStep("Attendance", () -> ApiClient.service(requireContext()).attendance(currentClassId == 0 ? null : currentClassId, null)));
        steps.add(new QaStep("Assignments", () -> ApiClient.service(requireContext()).assignments()));
        steps.add(new QaStep("Announcements", () -> ApiClient.service(requireContext()).announcements()));
        steps.add(new QaStep("Exams", () -> ApiClient.service(requireContext()).exams(), body -> {
            JsonObject first = firstDataObject(body);
            currentExamId = idFrom(first);
        }));
        steps.add(new QaStep("Grade categories", () -> currentClassId == 0 ? null : ApiClient.service(requireContext()).gradeCategories(currentClassId)));
        steps.add(new QaStep("Grade items", () -> currentClassId == 0 ? null : ApiClient.service(requireContext()).gradeItems(currentClassId)));
        steps.add(new QaStep("Final grade", () -> currentClassId == 0 || currentStudentId == 0 ? null : ApiClient.service(requireContext()).finalGrade(currentClassId, currentStudentId)));
        steps.add(new QaStep("Exam audit", () -> currentExamId == 0 ? null : ApiClient.service(requireContext()).examAudit(currentExamId)));
        steps.add(new QaStep("Report: student profile", () -> currentStudentId == 0 ? null : ApiClient.service(requireContext()).report("student-profile", null, currentStudentId)));
        steps.add(new QaStep("Report: attendance", () -> currentClassId == 0 ? null : ApiClient.service(requireContext()).report("attendance", currentClassId, null)));
        steps.add(new QaStep("Report: grades", () -> currentClassId == 0 ? null : ApiClient.service(requireContext()).report("grades", currentClassId, null)));
        steps.add(new QaStep("Report: class performance", () -> currentClassId == 0 ? null : ApiClient.service(requireContext()).report("class-performance", currentClassId, null)));
        steps.add(new QaStep("Report: missing assignments", () -> currentClassId == 0 ? null : ApiClient.service(requireContext()).report("missing-assignments", currentClassId, null)));
        steps.add(new QaStep("Report: failing grades", () -> currentClassId == 0 ? null : ApiClient.service(requireContext()).report("failing-grades", currentClassId, null)));
        steps.add(new QaStep("Report: frequent absences", () -> currentClassId == 0 ? null : ApiClient.service(requireContext()).report("frequent-absences", currentClassId, null)));
    }

    private void runStep(int index) {
        if (index >= steps.size()) {
            statusView.setText("Finished: " + passed + " passed, " + failed + " failed.");
            runButton.setEnabled(true);
            return;
        }

        QaStep step = steps.get(index);
        Call<JsonObject> call = step.createCall();
        if (call == null) {
            failed++;
            addCard("FAIL: " + step.label + "\nSkipped because a required id was not loaded.");
            statusView.setText("Running " + (index + 1) + " of " + steps.size());
            runStep(index + 1);
            return;
        }

        statusView.setText("Running " + (index + 1) + " of " + steps.size() + ": " + step.label);
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                JsonObject body = response.body();
                if (response.isSuccessful()) {
                    passed++;
                    step.capture(body);
                    addCard("PASS: " + step.label + "\nHTTP " + response.code() + "\n" + describe(body));
                } else {
                    failed++;
                    addCard("FAIL: " + step.label + "\nHTTP " + response.code());
                }
                runStep(index + 1);
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                failed++;
                addCard("FAIL: " + step.label + "\n" + t.getMessage());
                runStep(index + 1);
            }
        });
    }

    private String readRole() {
        try {
            String json = new TokenStore(requireContext()).getUserJson();
            return JsonParser.parseString(json).getAsJsonObject().get("role").getAsString();
        } catch (RuntimeException e) {
            return "";
        }
    }

    private JsonObject firstDataObject(JsonObject body) {
        if (body == null || !body.has("data") || !body.get("data").isJsonArray()) {
            return null;
        }
        JsonArray data = body.getAsJsonArray("data");
        if (data.size() == 0 || !data.get(0).isJsonObject()) {
            return null;
        }
        return data.get(0).getAsJsonObject();
    }

    private int idFrom(JsonObject object) {
        if (object == null || !object.has("id") || object.get("id").isJsonNull()) {
            return 0;
        }
        try {
            return object.get("id").getAsInt();
        } catch (RuntimeException e) {
            return 0;
        }
    }

    private String describe(JsonObject body) {
        if (body == null) {
            return "No JSON body.";
        }
        JsonElement data = body.get("data");
        if (data != null && data.isJsonArray()) {
            return data.getAsJsonArray().size() + " records.";
        }
        if (data != null && data.isJsonObject()) {
            return "Object loaded.";
        }
        if (body.has("message")) {
            return body.get("message").getAsString();
        }
        return "Response loaded.";
    }

    private interface CallFactory {
        Call<JsonObject> create();
    }

    private interface BodyCapture {
        void capture(JsonObject body);
    }

    private static class QaStep {
        private final String label;
        private final CallFactory factory;
        private final BodyCapture capture;

        QaStep(String label, CallFactory factory) {
            this(label, factory, null);
        }

        QaStep(String label, CallFactory factory, BodyCapture capture) {
            this.label = label;
            this.factory = factory;
            this.capture = capture;
        }

        Call<JsonObject> createCall() {
            return factory.create();
        }

        void capture(JsonObject body) {
            if (capture != null) {
                capture.capture(body);
            }
        }
    }
}
