package com.studentflow.app.ui;

import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.studentflow.app.api.ApiClient;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class TeacherExamsFragment extends BaseDataFragment {
    private int currentPage = 1;
    private int lastPage = 1;
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
        loadPage(1);
    }

    private void loadPage(int page) {
        currentPage = Math.max(1, page);
        setLoading(true);
        setStatus("", false);
        track(ApiClient.service(requireContext()).exams(currentPage, 25)).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                setLoading(false);
                if (!isViewActive()) {
                    return;
                }
                clearCards();
                if (!response.isSuccessful() || response.body() == null) {
                    showError("Exam list failed: HTTP " + response.code());
                    return;
                }
                JsonArray rows = response.body().getAsJsonArray("data");
                JsonObject meta = response.body().has("meta") && response.body().get("meta").isJsonObject()
                        ? response.body().getAsJsonObject("meta")
                        : new JsonObject();
                Integer pageValue = intValue(meta, "current_page");
                Integer lastValue = intValue(meta, "last_page");
                Integer total = intValue(meta, "total");
                currentPage = pageValue == null ? currentPage : pageValue;
                lastPage = lastValue == null ? 1 : lastValue;
                setStatus(total == null
                        ? rows.size() + " exam(s)."
                        : rows.size() + " shown of " + total + ". Page " + currentPage + " of " + lastPage + ".", false);
                for (JsonElement element : rows) {
                    JsonObject exam = element.getAsJsonObject();
                    addCard(summarize(exam), v -> audit(exam.get("id").getAsInt()));
                }
                if (rows.size() == 0) {
                    addCard("No exams created.");
                }
                addPaginationCard(
                        currentPage,
                        lastPage,
                        () -> loadPage(currentPage - 1),
                        () -> loadPage(currentPage + 1)
                );
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                setLoading(false);
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
            track(ApiClient.service(requireContext()).createExam(payload)).enqueue(new Callback<JsonObject>() {
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
        track(ApiClient.service(requireContext()).examAudit(examId)).enqueue(new Callback<JsonObject>() {
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
