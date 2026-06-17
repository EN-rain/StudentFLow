package com.studentflow.app.ui;

import com.google.gson.JsonObject;
import com.studentflow.app.api.ApiClient;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class ReportsFragment extends BaseDataFragment {
    public static ReportsFragment newInstance() {
        return new ReportsFragment();
    }

    @Override
    protected void configure() {
        setHeader("Reports", "Mobile summary sources for attendance, grades, and class performance.");
        addAction("Attendance", v -> load("attendance"));
        addAction("Grades", v -> load("grades"));
        addAction("Classes", v -> load("classes"));
        load("classes");
    }

    private void load(String type) {
        statusView.setText("Loading " + type + " report source...");
        Call<JsonObject> call;
        if ("attendance".equals(type)) {
            call = ApiClient.service(requireContext()).attendance(null, null);
        } else if ("grades".equals(type) || "classes".equals(type)) {
            call = ApiClient.service(requireContext()).classes();
        } else {
            showError("Unknown report type.");
            return;
        }
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful()) {
                    renderData(response.body(), "No report records found.");
                } else {
                    showError("Report source failed: HTTP " + response.code());
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                showError("Network error: " + t.getMessage());
            }
        });
    }
}
