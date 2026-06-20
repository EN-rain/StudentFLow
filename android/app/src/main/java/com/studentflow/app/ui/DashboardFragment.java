package com.studentflow.app.ui;

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
        setHeader("Dashboard", "Track teaching activity, student records, attendance, and class updates from one place.");
        addTopIconAction(com.studentflow.app.R.drawable.ic_refresh, "Refresh dashboard", v -> load());
        load();
    }

    private void load() {
        clearCards();
        setLoading(true);
        setStatus("Refreshing overview...", false);
        track(ApiClient.service(requireContext()).dashboardStats()).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                setLoading(false);
                if (!isViewActive()) {
                    return;
                }
                if (!response.isSuccessful() || response.body() == null || !response.body().has("data")) {
                    showError("Dashboard request failed: HTTP " + response.code());
                    return;
                }

                JsonObject data = response.body().getAsJsonObject("data");
                addMetric("Classes", intValue(data, "classes"), "Active sections under your account.");
                addMetric("Students", intValue(data, "students"), "Unique students visible to your classes.");
                addMetric("Attendance", intValue(data, "attendance_records"), "Saved attendance entries.");
                addMetric("Assignments", intValue(data, "assignments"), "Assignments currently tracked.");
                addMetric("Announcements", intValue(data, "announcements"), "Published class updates.");
                setStatus("Overview ready", false);
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                setLoading(false);
                if (isViewActive() && !call.isCanceled()) {
                    showError("Network error: " + t.getMessage());
                }
            }
        });
    }

    private void addMetric(String label, Integer value, String detail) {
        addCard(label + "\n" + (value == null ? 0 : value) + " total\n" + detail);
    }
}
