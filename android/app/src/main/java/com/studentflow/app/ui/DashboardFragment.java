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
        setHeader("Dashboard", "Track teaching activity, student records, attendance, and class updates from one place.");
        addAction("Refresh", v -> load());
        load();
    }

    private void load() {
        listContainer.removeAllViews();
        setStatus("Refreshing overview...", false);
        loadCount("Classes", "Active sections under your account.", ApiClient.service(requireContext()).classes());
        loadCount("Students", "Student records visible to your classes.", ApiClient.service(requireContext()).students(null, null));
        loadCount("Attendance", "Saved attendance entries.", ApiClient.service(requireContext()).attendance(null, null));
        loadCount("Assignments", "Assignments currently tracked.", ApiClient.service(requireContext()).assignments());
        loadCount("Announcements", "Published class updates.", ApiClient.service(requireContext()).announcements());
    }

    private void loadCount(String label, String detail, Call<JsonObject> call) {
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (!response.isSuccessful() || response.body() == null) {
                    addCard(label + "\nUnavailable\nHTTP " + response.code());
                    setStatus("Overview loaded with warnings", true);
                    return;
                }
                JsonElement data = response.body().get("data");
                int count = data != null && data.isJsonArray() ? data.getAsJsonArray().size() : 1;
                addCard(label + "\n" + count + " total\n" + detail);
                setStatus("Overview ready", false);
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                addCard(label + "\nNetwork error\nCheck API connectivity.");
                setStatus("Overview loaded with warnings", true);
            }
        });
    }
}
