package com.studentflow.app.ui;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.appcompat.app.AlertDialog;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;

import com.google.android.material.button.MaterialButton;
import com.google.android.material.card.MaterialCardView;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.studentflow.app.R;

public abstract class BaseDataFragment extends Fragment {
    protected TextView titleView;
    protected TextView subtitleView;
    protected TextView statusView;
    protected LinearLayout actionRow;
    protected LinearLayout listContainer;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_data_list, container, false);
        titleView = view.findViewById(R.id.screenTitle);
        subtitleView = view.findViewById(R.id.screenSubtitle);
        statusView = view.findViewById(R.id.statusText);
        actionRow = view.findViewById(R.id.actionRow);
        listContainer = view.findViewById(R.id.listContainer);
        configure();
        return view;
    }

    protected abstract void configure();

    protected void setHeader(String title, String subtitle) {
        titleView.setText(title);
        subtitleView.setText(subtitle);
    }

    protected MaterialButton addAction(String label, View.OnClickListener listener) {
        MaterialButton button = new MaterialButton(requireContext());
        button.setText(label);
        button.setOnClickListener(listener);
        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1);
        params.setMargins(0, 0, 8, 0);
        actionRow.addView(button, params);
        return button;
    }

    protected void renderData(JsonObject body, String emptyMessage) {
        listContainer.removeAllViews();
        JsonElement data = body == null ? null : body.get("data");
        if (data != null && data.isJsonArray()) {
            JsonArray array = data.getAsJsonArray();
            statusView.setText(array.size() + " records loaded.");
            for (JsonElement element : array) {
                addCard(summarize(element));
            }
            if (array.size() == 0) {
                addCard(emptyMessage);
            }
        } else if (data != null) {
            statusView.setText("Loaded.");
            addCard(summarize(data));
        } else if (body != null) {
            statusView.setText("Loaded.");
            addCard(summarize(body));
        } else {
            statusView.setText(emptyMessage);
        }
    }

    protected void showError(String message) {
        listContainer.removeAllViews();
        statusView.setText(message);
    }

    protected void addCard(String text) {
        addCard(text, null);
    }

    protected void addCard(String text, View.OnClickListener listener) {
        MaterialCardView card = new MaterialCardView(requireContext());
        card.setRadius(8);
        card.setCardElevation(1);
        if (listener != null) {
            card.setClickable(true);
            card.setOnClickListener(listener);
        }
        TextView content = new TextView(requireContext());
        content.setText(text);
        content.setTextSize(15);
        content.setTextColor(getResources().getColor(R.color.studentflow_text));
        int pad = (int) (12 * getResources().getDisplayMetrics().density);
        content.setPadding(pad, pad, pad, pad);
        card.addView(content);
        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT);
        params.setMargins(0, 0, 0, pad);
        listContainer.addView(card, params);
    }

    protected void confirm(String title, String message, Runnable onConfirm) {
        new AlertDialog.Builder(requireContext())
                .setTitle(title)
                .setMessage(message)
                .setNegativeButton("Cancel", null)
                .setPositiveButton("OK", (dialog, which) -> onConfirm.run())
                .show();
    }

    protected Integer intValue(JsonObject object, String key) {
        JsonElement value = object.get(key);
        if (value == null || value.isJsonNull()) {
            return null;
        }
        try {
            return value.getAsInt();
        } catch (RuntimeException e) {
            return null;
        }
    }

    protected String stringValue(JsonObject object, String key) {
        JsonElement value = object.get(key);
        return value == null || value.isJsonNull() ? "" : value.getAsString();
    }

    protected String summarize(JsonElement element) {
        if (element == null || element.isJsonNull()) {
            return "";
        }
        if (!element.isJsonObject()) {
            return element.toString();
        }
        JsonObject object = element.getAsJsonObject();
        StringBuilder builder = new StringBuilder();
        appendIfPresent(builder, object, "class_name", "Class");
        appendIfPresent(builder, object, "subject", "Subject");
        appendIfPresent(builder, object, "student_number", "Student #");
        appendName(builder, object);
        appendIfPresent(builder, object, "title", "Title");
        appendIfPresent(builder, object, "attendance_date", "Date");
        appendIfPresent(builder, object, "status", "Status");
        appendIfPresent(builder, object, "deadline", "Deadline");
        appendIfPresent(builder, object, "final_grade", "Final grade");
        appendIfPresent(builder, object, "email", "Email");
        if (builder.length() == 0) {
            builder.append(object.toString());
        }
        return builder.toString().trim();
    }

    private void appendName(StringBuilder builder, JsonObject object) {
        String first = value(object, "first_name");
        String last = value(object, "last_name");
        String name = value(object, "name");
        if (!name.isEmpty()) {
            appendLine(builder, "Name", name);
        } else if (!first.isEmpty() || !last.isEmpty()) {
            appendLine(builder, "Name", (first + " " + last).trim());
        }
    }

    private void appendIfPresent(StringBuilder builder, JsonObject object, String key, String label) {
        String value = value(object, key);
        if (!value.isEmpty()) {
            appendLine(builder, label, value);
        }
    }

    private void appendLine(StringBuilder builder, String label, String value) {
        if (builder.length() > 0) {
            builder.append('\n');
        }
        builder.append(label).append(": ").append(value);
    }

    private String value(JsonObject object, String key) {
        JsonElement value = object.get(key);
        return value == null || value.isJsonNull() ? "" : value.getAsString();
    }
}
