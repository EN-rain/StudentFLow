package com.studentflow.app.ui;

import android.os.Bundle;

import com.google.gson.JsonObject;
import com.studentflow.app.api.ApiClient;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class StudentEndpointFragment extends BaseDataFragment {
    private static final String ARG_TITLE = "title";
    private static final String ARG_SUBTITLE = "subtitle";
    private static final String ARG_ENDPOINT = "endpoint";

    public static StudentEndpointFragment newInstance(String title, String subtitle, String endpoint) {
        StudentEndpointFragment fragment = new StudentEndpointFragment();
        Bundle args = new Bundle();
        args.putString(ARG_TITLE, title);
        args.putString(ARG_SUBTITLE, subtitle);
        args.putString(ARG_ENDPOINT, endpoint);
        fragment.setArguments(args);
        return fragment;
    }

    @Override
    protected void configure() {
        setHeader(requireArguments().getString(ARG_TITLE), requireArguments().getString(ARG_SUBTITLE));
        addAction("Refresh", v -> load());
        if ("classes".equals(requireArguments().getString(ARG_ENDPOINT))) {
            addAction("Join Classroom", v -> openJoinForm());
            addAction("My Requests", v -> loadJoinRequests());
        }
        load();
    }

    private void load() {
        statusView.setText("Loading...");
        String endpoint = requireArguments().getString(ARG_ENDPOINT);
        Call<JsonObject> call;
        if ("dashboard".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentDashboard();
        } else if ("profile".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentProfile();
        } else if ("classes".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentClasses();
        } else if ("announcements".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentAnnouncements();
        } else if ("assignments".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentAssignments();
        } else if ("grades".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentGrades();
        } else if ("attendance".equals(endpoint)) {
            call = ApiClient.service(requireContext()).studentAttendance();
        } else {
            showError("Unknown student endpoint.");
            return;
        }
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful()) {
                    if ("dashboard".equals(endpoint)) {
                        renderDashboard(response.body());
                    } else {
                        renderData(response.body(), "No student records found.");
                    }
                } else {
                    showError("Request failed: HTTP " + response.code());
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                showError("Network error: " + t.getMessage());
            }
        });
    }

    private void renderDashboard(JsonObject body) {
        if (!isAdded() || getView() == null || body == null || !body.has("data")) {
            return;
        }

        JsonObject data = body.getAsJsonObject("data");
        JsonObject student = data.has("student") && data.get("student").isJsonObject()
                ? data.getAsJsonObject("student")
                : new JsonObject();

        listContainer.removeAllViews();
        statusView.setText("Overview loaded");

        String name = stringValue(student, "full_name");
        if (name.isEmpty()) {
            name = (stringValue(student, "first_name") + " " + stringValue(student, "last_name")).trim();
        }
        String studentNumber = stringValue(student, "student_number");
        String email = stringValue(student, "email");

        StringBuilder profile = new StringBuilder();
        if (!name.isEmpty()) profile.append(name);
        if (!studentNumber.isEmpty()) profile.append("\nStudent #: ").append(studentNumber);
        if (!email.isEmpty()) profile.append("\nEmail: ").append(email);
        addCard(profile.length() == 0 ? "Student profile unavailable." : profile.toString());

        addCard("Classes\n" + intOrZero(data, "classes_count"));
        addCard("Announcements\n" + intOrZero(data, "announcements_count"));
        addCard("Assignments\n" + intOrZero(data, "assignments_count"));
        addCard("Pending exams\n" + intOrZero(data, "pending_exams_count"));
    }

    private void loadJoinRequests() {
        setStatus("Loading join requests...", false);
        ApiClient.service(requireContext()).studentJoinRequests().enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (!isAdded() || getView() == null) {
                    return;
                }
                if (!response.isSuccessful() || response.body() == null) {
                    setStatus("Join requests failed: HTTP " + response.code(), true);
                    return;
                }

                JsonObject data = response.body().getAsJsonObject("data");
                listContainer.removeAllViews();
                boolean verified = data != null && data.has("verified") && data.get("verified").getAsBoolean();
                addCard(verified
                        ? "Verified\nGoogle and GitHub are linked."
                        : "Not verified\nLink both Google and GitHub before joining a classroom.");

                if (data != null && data.has("requests") && data.get("requests").isJsonArray()) {
                    for (com.google.gson.JsonElement element : data.getAsJsonArray("requests")) {
                        if (!element.isJsonObject()) {
                            continue;
                        }
                        JsonObject request = element.getAsJsonObject();
                        JsonObject schoolClass = request.has("school_class") && request.get("school_class").isJsonObject()
                                ? request.getAsJsonObject("school_class")
                                : new JsonObject();
                        addCard(stringValue(schoolClass, "class_name")
                                + "\n" + stringValue(schoolClass, "subject")
                                + "\nStatus: " + stringValue(request, "status"));
                    }
                }
                setStatus("Join requests loaded.", false);
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                if (isAdded() && getView() != null) {
                    setStatus("Network error: " + t.getMessage(), true);
                }
            }
        });
    }

    private void openJoinForm() {
        if (!isAdded()) {
            return;
        }

        FormDialog.show(requireContext(), "Join Classroom", new FormDialog.Field[] {
                FormDialog.text("join_code", "Classroom code", true)
        }, null, payload -> {
            setStatus("Sending join request...", false);
            ApiClient.service(requireContext()).requestClassJoin(payload).enqueue(new Callback<JsonObject>() {
                @Override
                public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                    if (!isAdded() || getView() == null) {
                        return;
                    }
                    if (response.isSuccessful()) {
                        setStatus("Join request sent. Waiting for teacher approval.", false);
                        load();
                    } else {
                        setStatus("Join request failed: HTTP " + response.code() + ". Link both Google and GitHub before joining.", true);
                    }
                }

                @Override
                public void onFailure(Call<JsonObject> call, Throwable t) {
                    if (isAdded() && getView() != null) {
                        setStatus("Network error: " + t.getMessage(), true);
                    }
                }
            });
        });
    }

    private int intOrZero(JsonObject object, String key) {
        return object.has(key) && !object.get(key).isJsonNull() ? object.get(key).getAsInt() : 0;
    }
}
