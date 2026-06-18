package com.studentflow.app.ui;

import android.content.Context;
import android.text.InputType;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.LinearLayout;
import android.widget.ScrollView;

import androidx.appcompat.app.AlertDialog;

import com.google.android.material.textfield.TextInputEditText;
import com.google.android.material.textfield.TextInputLayout;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.studentflow.app.R;

import java.util.LinkedHashMap;
import java.util.Map;

public class FormDialog {
    public interface SubmitListener {
        void onSubmit(JsonObject payload);
    }

    public static class Field {
        final String key;
        final String label;
        final boolean numeric;
        final boolean required;

        public Field(String key, String label, boolean numeric, boolean required) {
            this.key = key;
            this.label = label;
            this.numeric = numeric;
            this.required = required;
        }
    }

    public static Field text(String key, String label, boolean required) {
        return new Field(key, label, false, required);
    }

    public static Field number(String key, String label, boolean required) {
        return new Field(key, label, true, required);
    }

    public static void show(Context context, String title, Field[] fields, JsonObject existing, SubmitListener listener) {
        int pad = (int) (20 * context.getResources().getDisplayMetrics().density);
        LinearLayout layout = new LinearLayout(context);
        layout.setOrientation(LinearLayout.VERTICAL);
        layout.setPadding(pad, pad, pad, (int) (4 * context.getResources().getDisplayMetrics().density));
        Map<Field, TextInputEditText> inputs = new LinkedHashMap<>();

        for (Field field : fields) {
            TextInputLayout wrapper = new TextInputLayout(context, null, com.google.android.material.R.attr.textInputOutlinedStyle);
            wrapper.setHint(field.label + (field.required ? " *" : ""));
            wrapper.setBoxBackgroundMode(TextInputLayout.BOX_BACKGROUND_OUTLINE);
            wrapper.setBoxBackgroundColor(context.getColor(R.color.studentflow_field));
            wrapper.setBoxStrokeColor(context.getColor(R.color.studentflow_border_active));
            wrapper.setHintTextColor(android.content.res.ColorStateList.valueOf(context.getColor(R.color.studentflow_text_muted)));

            TextInputEditText input = new TextInputEditText(context);
            input.setSingleLine(true);
            input.setInputType(field.numeric ? InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_DECIMAL : InputType.TYPE_CLASS_TEXT);
            input.setMinHeight((int) (56 * context.getResources().getDisplayMetrics().density));
            input.setTextColor(context.getColor(R.color.studentflow_text));
            input.setTextSize(16);
            if (existing != null) {
                JsonElement value = existing.get(field.key);
                if (value != null && !value.isJsonNull()) {
                    input.setText(value.getAsString());
                }
            }
            wrapper.addView(input, new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT));
            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT);
            params.setMargins(0, 0, 0, pad);
            layout.addView(wrapper, params);
            inputs.put(field, input);
        }

        ScrollView scrollView = new ScrollView(context);
        scrollView.addView(layout);
        AlertDialog dialog = new AlertDialog.Builder(context)
                .setTitle(title)
                .setView(scrollView)
                .setNegativeButton("Cancel", null)
                .setPositiveButton("Save", null)
                .create();
        dialog.setOnShowListener(d -> dialog.getButton(AlertDialog.BUTTON_POSITIVE).setOnClickListener(v -> {
            Button positive = dialog.getButton(AlertDialog.BUTTON_POSITIVE);
            Button negative = dialog.getButton(AlertDialog.BUTTON_NEGATIVE);
            JsonObject payload = new JsonObject();
            boolean valid = true;
            for (Map.Entry<Field, TextInputEditText> entry : inputs.entrySet()) {
                Field field = entry.getKey();
                String value = entry.getValue().getText().toString().trim();
                if (field.required && value.isEmpty()) {
                    entry.getValue().setError("Required");
                    valid = false;
                    continue;
                }
                if (value.isEmpty()) {
                    continue;
                }
                if (field.numeric) {
                    try {
                        if (value.contains(".")) {
                            payload.addProperty(field.key, Double.parseDouble(value));
                        } else {
                            payload.addProperty(field.key, Integer.parseInt(value));
                        }
                    } catch (NumberFormatException e) {
                        entry.getValue().setError("Number required");
                        valid = false;
                    }
                } else {
                    payload.addProperty(field.key, value);
                }
            }
            if (valid) {
                positive.setEnabled(false);
                negative.setEnabled(false);
                for (TextInputEditText input : inputs.values()) {
                    input.setEnabled(false);
                }
                dialog.dismiss();
                listener.onSubmit(payload);
            }
        }));
        dialog.show();
    }
}
