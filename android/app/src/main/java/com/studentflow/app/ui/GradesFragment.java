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
        addAction("Category", v -> createCategory());
        addAction("Item", v -> createItem());
        addAction("Score", v -> saveScore());
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

    private void createCategory() {
        FormDialog.show(requireContext(), "Create Grade Category", new FormDialog.Field[] {
                FormDialog.number("class_id", "Class ID", true),
                FormDialog.text("category_name", "Category name", true),
                FormDialog.number("percentage_weight", "Weight percent", true)
        }, null, payload -> {
            int classId = payload.get("class_id").getAsInt();
            payload.remove("class_id");
            submit(ApiClient.service(requireContext()).createGradeCategory(classId, payload));
        });
    }

    private void createItem() {
        FormDialog.show(requireContext(), "Create Grade Item", new FormDialog.Field[] {
                FormDialog.number("class_id", "Class ID", true),
                FormDialog.number("category_id", "Category ID", true),
                FormDialog.text("title", "Title", true),
                FormDialog.number("maximum_score", "Maximum score", true),
                FormDialog.text("date_given", "Date given: YYYY-MM-DD", false)
        }, null, payload -> {
            int classId = payload.get("class_id").getAsInt();
            payload.remove("class_id");
            submit(ApiClient.service(requireContext()).createGradeItem(classId, payload));
        });
    }

    private void saveScore() {
        FormDialog.show(requireContext(), "Save Student Score", new FormDialog.Field[] {
                FormDialog.number("class_id", "Class ID", true),
                FormDialog.number("student_id", "Student ID", true),
                FormDialog.number("grade_item_id", "Grade item ID", true),
                FormDialog.number("score", "Score", true),
                FormDialog.text("remarks", "Remarks", false)
        }, null, payload -> {
            JsonObject score = new JsonObject();
            score.addProperty("grade_item_id", payload.get("grade_item_id").getAsInt());
            score.addProperty("score", payload.get("score").getAsDouble());
            if (payload.has("remarks")) {
                score.addProperty("remarks", payload.get("remarks").getAsString());
            }
            JsonArray scores = new JsonArray();
            scores.add(score);
            JsonObject request = new JsonObject();
            request.add("scores", scores);
            submit(ApiClient.service(requireContext()).saveStudentGrades(
                    payload.get("class_id").getAsInt(),
                    payload.get("student_id").getAsInt(),
                    request
            ));
        });
    }

    private void submit(Call<JsonObject> call) {
        statusView.setText("Saving...");
        call.enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful()) {
                    addCard(response.body() == null ? "Saved." : response.body().toString());
                    statusView.setText("Saved.");
                } else {
                    showError("Save failed: HTTP " + response.code());
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                showError("Network error: " + t.getMessage());
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
