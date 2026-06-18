package com.studentflow.app.ui;

import android.content.Intent;

import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.studentflow.app.api.ApiClient;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class ProfileFragment extends BaseDataFragment {
    public static ProfileFragment newInstance() {
        return new ProfileFragment();
    }

    @Override
    protected void configure() {
        setHeader("Profile", "Review your account identity, access, and connected profile details.");
        addAction("Refresh", v -> load());
        addAction("Password", v -> startActivity(new Intent(requireContext(), ChangePasswordActivity.class)));
        load();
    }

    private void load() {
        setStatus("Loading profile...", false);
        ApiClient.service(requireContext()).me().enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful() && response.body() != null) {
                    renderProfile(response.body());
                } else {
                    showError("Profile request failed: HTTP " + response.code());
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                showError("Network error: " + t.getMessage());
            }
        });
    }

    private void renderProfile(JsonObject body) {
        if (!isAdded() || getView() == null) {
            return;
        }
        listContainer.removeAllViews();
        setStatus("Account loaded", false);
        addCard(summary("Account", body));
        JsonElement teacher = body.get("teacher");
        if (teacher != null && teacher.isJsonObject()) {
            addCard(summary("Teacher profile", teacher.getAsJsonObject()));
        }
        JsonElement student = body.get("student");
        if (student != null && student.isJsonObject()) {
            addCard(summary("Student profile", student.getAsJsonObject()));
        }
    }

    private String summary(String heading, JsonObject object) {
        StringBuilder builder = new StringBuilder();
        builder.append(heading);
        append(builder, "Name", stringValue(object, "name"));
        append(builder, "Username", stringValue(object, "username"));
        append(builder, "Email", stringValue(object, "email"));
        append(builder, "Role", stringValue(object, "role"));
        append(builder, "Status", stringValue(object, "status"));
        if (object.has("classroom_verified")) {
            append(builder, "Classroom verification", object.get("classroom_verified").getAsBoolean() ? "Verified" : "Not verified");
            append(builder, "Google", object.has("google_linked") && object.get("google_linked").getAsBoolean() ? "Linked" : "Not linked");
            append(builder, "GitHub", object.has("github_linked") && object.get("github_linked").getAsBoolean() ? "Linked" : "Not linked");
        }
        append(builder, "Employee #", stringValue(object, "employee_number"));
        append(builder, "Department", stringValue(object, "department"));
        append(builder, "Student #", stringValue(object, "student_number"));
        append(builder, "GitHub", stringValue(object, "github_username"));
        append(builder, "Google email", stringValue(object, "google_email"));
        return builder.toString();
    }

    private void append(StringBuilder builder, String label, String value) {
        if (value == null || value.isEmpty()) {
            return;
        }
        builder.append("\n").append(label).append(": ").append(value);
    }
}
