package com.studentflow.app.ui;

import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.studentflow.app.api.ApiClient;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class DashboardFragment extends BaseDataFragment {
    public static DashboardFragment newInstance() {
        return new DashboardFragment();
    }

    @Override
    protected void configure() {
        setHeader("Dashboard", "Snapshot of your classes, students, attendance, assignments, and announcements.");
        addAction("Refresh", v -> load());
        load();
    }

    private void load() {
        listContainer.removeAllViews();
        statusView.setText("Loading dashboard...");
        loadCount("Classes", ApiClient.service(requireContext()).classes());
        loadCount("Students", ApiClient.service(requireContext()).students(null, null));
        loadCount("Attendance records", ApiClient.service(requireContext()).attendance(null, null));
        loadCount("Assignments", ApiClient.service(requireContext()).assignments());
        loadCount("Announcements", ApiClient.service(requireContext()).announcements());
    }

    private void loadCount(String label, Call<JsonObject> call) {
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (!response.isSuccessful() || response.body() == null) {
                    addCard(label + ": unavailable (HTTP " + response.code() + ")");
                    statusView.setText("Dashboard loaded with warnings.");
                    return;
                }
                JsonElement data = response.body().get("data");
                int count = data != null && data.isJsonArray() ? data.getAsJsonArray().size() : 1;
                addCard(label + ": " + count);
                statusView.setText("Dashboard loaded.");
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                addCard(label + ": network error");
                statusView.setText("Dashboard loaded with warnings.");
            }
        });
    }
}
