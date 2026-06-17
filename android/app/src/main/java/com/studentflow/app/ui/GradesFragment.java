package com.studentflow.app.ui;

import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.studentflow.app.api.ApiClient;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class GradesFragment extends BaseDataFragment {
    public static GradesFragment newInstance() {
        return new GradesFragment();
    }

    @Override
    protected void configure() {
        setHeader("Grades", "Loads classes first, then grade categories for the first class.");
        addAction("Refresh", v -> loadClasses());
        loadClasses();
    }

    private void loadClasses() {
        statusView.setText("Loading classes...");
        listContainer.removeAllViews();
        ApiClient.service(requireContext()).classes().enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (!response.isSuccessful() || response.body() == null) {
                    showError("Classes request failed: HTTP " + response.code());
                    return;
                }
                renderData(response.body(), "No classes available for grades.");
                Integer firstClassId = firstId(response.body());
                if (firstClassId != null) {
                    loadGradeCategories(firstClassId);
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                showError("Network error: " + t.getMessage());
            }
        });
    }

    private void loadGradeCategories(int classId) {
        ApiClient.service(requireContext()).gradeCategories(classId).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful()) {
                    addCard("Grade categories for class #" + classId + "\n" + (response.body() == null ? "{}" : response.body().toString()));
                } else {
                    addCard("Grade categories unavailable for class #" + classId + " (HTTP " + response.code() + ")");
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                addCard("Grade categories network error: " + t.getMessage());
            }
        });
    }

    private Integer firstId(JsonObject body) {
        JsonElement data = body.get("data");
        if (data == null || !data.isJsonArray()) {
            return null;
        }
        JsonArray array = data.getAsJsonArray();
        if (array.size() == 0 || !array.get(0).isJsonObject()) {
            return null;
        }
        JsonElement id = array.get(0).getAsJsonObject().get("id");
        return id == null ? null : id.getAsInt();
    }
}
