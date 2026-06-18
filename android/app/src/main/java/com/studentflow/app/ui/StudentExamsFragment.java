package com.studentflow.app.ui;

import android.text.InputType;
import android.view.ViewGroup;
import android.widget.EditText;
import android.widget.LinearLayout;

import androidx.appcompat.app.AlertDialog;

import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.studentflow.app.api.ApiClient;

import java.util.ArrayList;
import java.util.List;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class StudentExamsFragment extends BaseDataFragment {
    public static StudentExamsFragment newInstance() {
        return new StudentExamsFragment();
    }

    @Override
    protected void configure() {
        setHeader("Exams", "Open assigned exams, answer questions, and submit scores to StudentFlow.");
        addAction("Refresh", v -> load());
        load();
    }

    private void load() {
        setLoading(true);
        setStatus("", false);
        ApiClient.service(requireContext()).studentExams().enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                setLoading(false);
                listContainer.removeAllViews();
                if (!response.isSuccessful() || response.body() == null) {
                    showError("Exam list failed: HTTP " + response.code());
                    return;
                }
                JsonArray rows = response.body().getAsJsonArray("data");
                setStatus(rows.size() + " exam attempt(s).", false);
                for (JsonElement element : rows) {
                    JsonObject attempt = element.getAsJsonObject();
                    addCard(summarize(attempt), v -> openExam(attempt));
                }
                if (rows.size() == 0) {
                    addCard("No exams assigned.");
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                setLoading(false);
                showError("Network error: " + t.getMessage());
            }
        });
    }

    private void openExam(JsonObject attempt) {
        String token = stringValue(attempt, "magic_token");
        if (token.isEmpty()) {
            showError("This exam attempt has no magic token.");
            return;
        }
        ApiClient.service(requireContext()).startMagicExam(token).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (!response.isSuccessful()) {
                    showError("Exam start failed: HTTP " + response.code());
                    return;
                }
                loadExam(token);
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                showError("Network error: " + t.getMessage());
            }
        });
    }

    private void loadExam(String token) {
        ApiClient.service(requireContext()).magicExam(token).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (!response.isSuccessful() || response.body() == null) {
                    showError("Exam load failed: HTTP " + response.code());
                    return;
                }
                showAnswerDialog(token, response.body().getAsJsonObject("data"));
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                showError("Network error: " + t.getMessage());
            }
        });
    }

    private void showAnswerDialog(String token, JsonObject attempt) {
        JsonArray questions = attempt.getAsJsonObject("exam").getAsJsonArray("questions");
        LinearLayout layout = new LinearLayout(requireContext());
        layout.setOrientation(LinearLayout.VERTICAL);
        int pad = (int) (12 * getResources().getDisplayMetrics().density);
        layout.setPadding(pad, pad, pad, pad);
        List<EditText> inputs = new ArrayList<>();
        List<Integer> ids = new ArrayList<>();
        for (JsonElement element : questions) {
            JsonObject question = element.getAsJsonObject();
            EditText input = new EditText(requireContext());
            input.setHint(question.get("prompt").getAsString());
            input.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_FLAG_MULTI_LINE);
            layout.addView(input, new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT));
            inputs.add(input);
            ids.add(question.get("id").getAsInt());
        }
        new AlertDialog.Builder(requireContext())
                .setTitle(attempt.getAsJsonObject("exam").get("title").getAsString())
                .setView(layout)
                .setNegativeButton("Cancel", null)
                .setPositiveButton("Submit", (dialog, which) -> submit(token, ids, inputs))
                .show();
    }

    private void submit(String token, List<Integer> ids, List<EditText> inputs) {
        JsonArray answers = new JsonArray();
        for (int i = 0; i < ids.size(); i++) {
            JsonObject answer = new JsonObject();
            answer.addProperty("question_id", ids.get(i));
            answer.addProperty("answer", inputs.get(i).getText().toString());
            answers.add(answer);
        }
        JsonObject payload = new JsonObject();
        payload.add("answers", answers);
        ApiClient.service(requireContext()).submitMagicExam(token, payload).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful()) {
                    load();
                } else {
                    showError("Submit failed: HTTP " + response.code());
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                showError("Network error: " + t.getMessage());
            }
        });
    }
}
