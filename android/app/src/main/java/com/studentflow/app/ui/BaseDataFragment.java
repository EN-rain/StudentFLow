package com.studentflow.app.ui;

import android.graphics.Color;
import android.os.Bundle;
import android.util.TypedValue;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.TextView;

import androidx.appcompat.app.AlertDialog;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
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
    protected ProgressBar loadingLine;
    protected LinearLayout topActionRow;
    protected LinearLayout actionRow;
    protected LinearLayout listContainer;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_data_list, container, false);
        titleView = view.findViewById(R.id.screenTitle);
        subtitleView = view.findViewById(R.id.screenSubtitle);
        statusView = view.findViewById(R.id.statusText);
        loadingLine = view.findViewById(R.id.loadingLine);
        topActionRow = view.findViewById(R.id.topActionRow);
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
        MaterialButton button = new MaterialButton(requireContext(), null, com.google.android.material.R.attr.materialButtonOutlinedStyle);
        button.setText(label);
        button.setOnClickListener(listener);
        button.setInsetTop(0);
        button.setInsetBottom(0);
        button.setCornerRadius(dp(18));
        button.setStrokeWidth(dp(1));
        button.setStrokeColorResource(R.color.studentflow_border);
        button.setBackgroundTintList(ContextCompat.getColorStateList(requireContext(), R.color.studentflow_field));
        button.setTextColor(ContextCompat.getColor(requireContext(), R.color.studentflow_primary));
        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.WRAP_CONTENT, dp(48));
        params.setMargins(0, 0, dp(10), 0);
        actionRow.addView(button, params);
        return button;
    }

    protected MaterialButton addTopIconAction(int iconRes, String description, View.OnClickListener listener) {
        MaterialButton button = new MaterialButton(requireContext(), null, com.google.android.material.R.attr.materialButtonOutlinedStyle);
        button.setIconResource(iconRes);
        button.setIconTintResource(R.color.studentflow_primary);
        button.setContentDescription(description);
        button.setText("");
        button.setOnClickListener(listener);
        button.setInsetTop(0);
        button.setInsetBottom(0);
        button.setMinWidth(dp(48));
        button.setMinimumWidth(dp(48));
        button.setCornerRadius(dp(24));
        button.setStrokeWidth(dp(1));
        button.setStrokeColorResource(R.color.studentflow_border);
        button.setBackgroundTintList(ContextCompat.getColorStateList(requireContext(), R.color.studentflow_field));
        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(dp(48), dp(48));
        params.setMargins(dp(8), 0, 0, 0);
        topActionRow.addView(button, params);
        return button;
    }

    protected void renderData(JsonObject body, String emptyMessage) {
        if (!isAdded() || getView() == null || listContainer == null || statusView == null) {
            return;
        }
        listContainer.removeAllViews();
        setLoading(false);
        JsonElement data = body == null ? null : body.get("data");
        if (data != null && data.isJsonArray()) {
            JsonArray array = data.getAsJsonArray();
            setStatus(statusForArray(body, array), false);
            for (JsonElement element : array) {
                addCard(summarize(element));
            }
            if (array.size() == 0) {
                addCard(emptyMessage);
            }
        } else if (data != null) {
            setStatus("Loaded", false);
            addCard(summarize(data));
        } else if (body != null) {
            setStatus("Loaded", false);
            addCard(summarize(body));
        } else {
            setStatus(emptyMessage, true);
        }
    }

    private String statusForArray(JsonObject body, JsonArray array) {
        JsonElement metaElement = body.get("meta");
        if (metaElement != null && metaElement.isJsonObject()) {
            JsonObject meta = metaElement.getAsJsonObject();
            Integer currentPage = intValue(meta, "current_page");
            Integer lastPage = intValue(meta, "last_page");
            Integer total = intValue(meta, "total");
            if (currentPage != null && lastPage != null && total != null) {
                return array.size() + " shown of " + total + " records. Page " + currentPage + " of " + lastPage + ".";
            }
        }
        return array.size() + " records loaded.";
    }

    protected void showError(String message) {
        if (!isAdded() || getView() == null || listContainer == null || statusView == null) {
            return;
        }
        listContainer.removeAllViews();
        setLoading(false);
        setStatus(message, true);
    }

    protected void setStatus(String message, boolean error) {
        if (!isAdded() || statusView == null) {
            return;
        }
        statusView.setVisibility(message == null || message.trim().isEmpty() ? View.GONE : View.VISIBLE);
        statusView.setText(message);
        statusView.setBackgroundTintList(ContextCompat.getColorStateList(requireContext(), error ? R.color.studentflow_error : R.color.studentflow_surface_alt));
        statusView.setTextColor(ContextCompat.getColor(requireContext(), error ? android.R.color.white : R.color.studentflow_text));
    }

    protected void setLoading(boolean loading) {
        if (loadingLine != null) {
            loadingLine.setVisibility(loading ? View.VISIBLE : View.GONE);
        }
    }

    protected void addCard(String text) {
        addCard(text, null);
    }

    protected void addCard(String text, View.OnClickListener listener) {
        if (!isAdded() || getView() == null || listContainer == null) {
            return;
        }
        MaterialCardView card = new MaterialCardView(requireContext());
        card.setRadius(dp(22));
        card.setCardElevation(0f);
        card.setStrokeWidth(dp(1));
        card.setStrokeColor(ContextCompat.getColor(requireContext(), R.color.studentflow_border));
        card.setCardBackgroundColor(ContextCompat.getColor(requireContext(), R.color.studentflow_field));
        card.setUseCompatPadding(false);
        if (listener != null) {
            card.setClickable(true);
            card.setFocusable(true);
            card.setOnClickListener(listener);
            card.setRippleColor(ContextCompat.getColorStateList(requireContext(), R.color.studentflow_surface_alt));
        }

        LinearLayout wrapper = new LinearLayout(requireContext());
        wrapper.setOrientation(LinearLayout.VERTICAL);
        int pad = dp(18);
        wrapper.setPadding(pad, pad, pad, pad);

        String[] lines = text.split("\\n");
        for (int i = 0; i < lines.length; i++) {
            TextView content = new TextView(requireContext());
            content.setText(lines[i]);
            content.setTextColor(ContextCompat.getColor(requireContext(), i == 0 ? R.color.studentflow_text : R.color.studentflow_text_muted));
            content.setTextSize(TypedValue.COMPLEX_UNIT_SP, i == 0 ? 16 : 14);
            content.setLineSpacing(0f, 1.2f);
            if (i == 0) {
                content.setTypeface(content.getTypeface(), android.graphics.Typeface.BOLD);
            }
            wrapper.addView(content);
        }

        card.addView(wrapper);
        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT);
        params.setMargins(0, 0, 0, dp(12));
        listContainer.addView(card, params);
    }

    protected void confirm(String title, String message, Runnable onConfirm) {
        if (!isAdded()) {
            return;
        }
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
        appendIfPresent(builder, object, "join_code", "Join code");
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

    protected int dp(int value) {
        return Math.round(value * getResources().getDisplayMetrics().density);
    }
}
