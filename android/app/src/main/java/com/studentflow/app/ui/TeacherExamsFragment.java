package com.studentflow.app.ui;

import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.studentflow.app.api.ApiClient;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class TeacherExamsFragment extends BaseDataFragment {
    public static TeacherExamsFragment newInstance() {
        return new TeacherExamsFragment();
    }

    @Override
    protected void configure() {
        setHeader("Exams", "Create quick exams, publish magic links, and audit student attempts.");
        addAction("Refresh", v -> load());
        addAction("Add", v -> openCreate());
        load();
    }

    private void load() {
        statusView.setText("Loading exams...");
        ApiClient.service(requireContext()).exams().enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                listContainer.removeAllViews();
                if (!response.isSuccessful() || response.body() == null) {
                    showError("Exam list failed: HTTP " + response.code());
                    return;
                }
                JsonArray rows = response.body().getAsJsonArray("data");
                statusView.setText(rows.size() + " exam(s).");
                for (JsonElement element : rows) {
                    JsonObject exam = element.getAsJsonObject();
                    addCard(summarize(exam), v -> audit(exam.get("id").getAsInt()));
                }
                if (rows.size() == 0) {
                    addCard("No exams created.");
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                showError("Network error: " + t.getMessage());
            }
        });
    }

    private void openCreate() {
        FormDialog.show(requireContext(), "Create Exam", new FormDialog.Field[] {
                FormDialog.number("class_id", "Class ID", true),
                FormDialog.number("grade_item_id", "Grade item ID for score sync", false),
                FormDialog.text("title", "Exam title", true),
                FormDialog.text("instructions", "Instructions", false),
                FormDialog.number("maximum_score", "Maximum score", true),
                FormDialog.text("question_prompt", "Question prompt", true),
                FormDialog.text("correct_answer", "Correct answer", true),
                FormDialog.number("points", "Question points", true)
        }, null, payload -> {
            JsonObject question = new JsonObject();
            question.addProperty("prompt", payload.get("question_prompt").getAsString());
            question.addProperty("type", "text");
            question.addProperty("correct_answer", payload.get("correct_answer").getAsString());
            question.addProperty("points", payload.get("points").getAsDouble());
            JsonArray questions = new JsonArray();
            questions.add(question);
            payload.remove("question_prompt");
            payload.remove("correct_answer");
            payload.remove("points");
            payload.addProperty("status", "published");
            payload.add("questions", questions);
            ApiClient.service(requireContext()).createExam(payload).enqueue(new Callback<JsonObject>() {
                @Override
                public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                    if (response.isSuccessful()) {
                        load();
                    } else {
                        showError("Create failed: HTTP " + response.code());
                    }
                }

                @Override
                public void onFailure(Call<JsonObject> call, Throwable t) {
                    showError("Network error: " + t.getMessage());
                }
            });
        });
    }

    private void audit(int examId) {
        ApiClient.service(requireContext()).examAudit(examId).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful()) {
                    renderData(response.body(), "No audit records.");
                } else {
                    showError("Audit failed: HTTP " + response.code());
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                showError("Network error: " + t.getMessage());
            }
        });
    }
}
