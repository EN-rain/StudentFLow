package com.studentflow.app.ui;

import android.os.CountDownTimer;
import android.text.InputType;
import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.RadioButton;
import android.widget.RadioGroup;
import android.widget.ScrollView;
import android.widget.TextView;

import androidx.appcompat.app.AlertDialog;

import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.studentflow.app.api.ApiClient;

import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class StudentExamsFragment extends BaseDataFragment {
    private CountDownTimer countDownTimer;

    public static StudentExamsFragment newInstance() {
        return new StudentExamsFragment();
    }

    @Override
    protected void configure() {
        setHeader("Exams", "Open assigned exams, answer questions, and submit scores to StudentFlow.");
        addAction("Refresh", v -> load());
        load();
    }

    @Override
    public void onDestroyView() {
        cancelTimer();
        super.onDestroyView();
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
        if (!attempt.has("id")) {
            showError("This exam attempt is missing an ID.");
            return;
        }

        int attemptId = attempt.get("id").getAsInt();
        ApiClient.service(requireContext()).startStudentExam(attemptId).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (!response.isSuccessful() || response.body() == null) {
                    showError("Exam start failed: HTTP " + response.code());
                    return;
                }
                showAnswerDialog(attemptId, response.body().getAsJsonObject("data"));
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                showError("Network error: " + t.getMessage());
            }
        });
    }

    private void showAnswerDialog(int attemptId, JsonObject attempt) {
        JsonObject exam = attempt.getAsJsonObject("exam");
        JsonArray questions = exam.getAsJsonArray("questions");

        LinearLayout form = new LinearLayout(requireContext());
        form.setOrientation(LinearLayout.VERTICAL);
        int pad = (int) (16 * getResources().getDisplayMetrics().density);
        form.setPadding(pad, pad, pad, pad);

        TextView timerView = new TextView(requireContext());
        timerView.setTextSize(16);
        timerView.setPadding(0, 0, 0, pad);
        form.addView(timerView);

        List<AnswerInput> inputs = new ArrayList<>();
        for (JsonElement element : questions) {
            JsonObject question = element.getAsJsonObject();
            int questionId = question.get("id").getAsInt();
            String prompt = question.get("prompt").getAsString();
            String type = question.has("type") ? question.get("type").getAsString() : "text";

            TextView label = new TextView(requireContext());
            label.setText(prompt);
            label.setTextSize(16);
            label.setPadding(0, pad / 2, 0, pad / 3);
            form.addView(label);

            if ("multiple_choice".equals(type) && question.has("choices") && question.get("choices").isJsonArray()) {
                RadioGroup group = new RadioGroup(requireContext());
                group.setOrientation(RadioGroup.VERTICAL);
                for (JsonElement choiceElement : question.getAsJsonArray("choices")) {
                    RadioButton button = new RadioButton(requireContext());
                    button.setText(choiceElement.getAsString());
                    button.setTag(choiceElement.getAsString());
                    group.addView(button);
                }
                form.addView(group, new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT));
                inputs.add(new AnswerInput(questionId, group));
            } else {
                EditText input = new EditText(requireContext());
                input.setInputType(InputType.TYPE_CLASS_TEXT | InputType.TYPE_TEXT_FLAG_MULTI_LINE);
                input.setMinLines(2);
                form.addView(input, new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT));
                inputs.add(new AnswerInput(questionId, input));
            }
        }

        ScrollView scrollView = new ScrollView(requireContext());
        scrollView.addView(form);

        AlertDialog dialog = new AlertDialog.Builder(requireContext())
                .setTitle(exam.get("title").getAsString())
                .setView(scrollView)
                .setNegativeButton("Cancel", null)
                .setPositiveButton("Submit", null)
                .create();

        dialog.setOnShowListener(ignored -> dialog.getButton(AlertDialog.BUTTON_POSITIVE).setOnClickListener(v -> {
            dialog.getButton(AlertDialog.BUTTON_POSITIVE).setEnabled(false);
            submit(attemptId, inputs, dialog);
        }));
        dialog.setOnDismissListener(ignored -> cancelTimer());
        dialog.show();

        long remainingSeconds = attempt.has("remaining_seconds") && !attempt.get("remaining_seconds").isJsonNull()
                ? Math.max(0L, attempt.get("remaining_seconds").getAsLong())
                : -1L;
        startTimer(remainingSeconds, timerView, () -> {
            if (dialog.isShowing()) {
                dialog.getButton(AlertDialog.BUTTON_POSITIVE).setEnabled(false);
                submit(attemptId, inputs, dialog);
            }
        });
    }

    private void startTimer(long remainingSeconds, TextView timerView, Runnable onFinish) {
        cancelTimer();
        if (remainingSeconds < 0) {
            timerView.setText("No time limit");
            return;
        }
        if (remainingSeconds == 0) {
            timerView.setText("Time remaining: 00:00");
            onFinish.run();
            return;
        }

        countDownTimer = new CountDownTimer(remainingSeconds * 1000L, 1000L) {
            @Override
            public void onTick(long millisUntilFinished) {
                long totalSeconds = millisUntilFinished / 1000L;
                long minutes = totalSeconds / 60L;
                long seconds = totalSeconds % 60L;
                timerView.setText(String.format(Locale.US, "Time remaining: %02d:%02d", minutes, seconds));
            }

            @Override
            public void onFinish() {
                timerView.setText("Time remaining: 00:00");
                onFinish.run();
            }
        }.start();
    }

    private void cancelTimer() {
        if (countDownTimer != null) {
            countDownTimer.cancel();
            countDownTimer = null;
        }
    }

    private void submit(int attemptId, List<AnswerInput> inputs, AlertDialog dialog) {
        JsonArray answers = new JsonArray();
        for (AnswerInput input : inputs) {
            JsonObject answer = new JsonObject();
            answer.addProperty("question_id", input.questionId);
            answer.addProperty("answer", input.value());
            answers.add(answer);
        }

        JsonObject payload = new JsonObject();
        payload.add("answers", answers);
        ApiClient.service(requireContext()).submitStudentExam(attemptId, payload).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (response.isSuccessful()) {
                    cancelTimer();
                    dialog.dismiss();
                    load();
                } else {
                    dialog.getButton(AlertDialog.BUTTON_POSITIVE).setEnabled(true);
                    showError("Submit failed: HTTP " + response.code());
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                dialog.getButton(AlertDialog.BUTTON_POSITIVE).setEnabled(true);
                showError("Network error: " + t.getMessage());
            }
        });
    }

    private static class AnswerInput {
        private final int questionId;
        private final View view;

        AnswerInput(int questionId, View view) {
            this.questionId = questionId;
            this.view = view;
        }

        String value() {
            if (view instanceof EditText) {
                return ((EditText) view).getText().toString().trim();
            }
            if (view instanceof RadioGroup) {
                RadioGroup group = (RadioGroup) view;
                int checkedId = group.getCheckedRadioButtonId();
                if (checkedId == View.NO_ID) {
                    return "";
                }
                RadioButton button = group.findViewById(checkedId);
                return button == null ? "" : button.getText().toString();
            }
            return "";
        }
    }
}
