package com.studentflow.app.ui;

import com.google.gson.JsonObject;
import com.studentflow.app.api.ApiClient;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class StudentsFragment extends BaseDataFragment {
    public static StudentsFragment newInstance() {
        return new StudentsFragment();
    }

    @Override
    protected void configure() {
        setHeader("Students", "Students visible through your assigned classes.");
        addAction("Refresh", v -> load(null));
        addAction("Search A", v -> load("a"));
        load(null);
    }

    private void load(String query) {
        setLoading(true);
        setStatus("", false);
        ApiClient.service(requireContext()).students(query, null).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                setLoading(false);
                if (response.isSuccessful()) {
                    renderData(response.body(), "No students found.");
                } else {
                    showError("Students request failed: HTTP " + response.code());
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                setLoading(false);
                showError("Network error: " + t.getMessage());
            }
        });
    }
}
