package com.studentflow.app.ui;

import android.content.Context;
import android.text.InputType;
import android.view.ViewGroup;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.ScrollView;

import androidx.appcompat.app.AlertDialog;

import com.google.gson.JsonElement;
import com.google.gson.JsonObject;

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
        int pad = (int) (16 * context.getResources().getDisplayMetrics().density);
        LinearLayout layout = new LinearLayout(context);
        layout.setOrientation(LinearLayout.VERTICAL);
        layout.setPadding(pad, pad, pad, 0);
        Map<Field, EditText> inputs = new LinkedHashMap<>();

        for (Field field : fields) {
            EditText input = new EditText(context);
            input.setHint(field.label + (field.required ? " *" : ""));
            input.setSingleLine(false);
            input.setInputType(field.numeric ? InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_DECIMAL : InputType.TYPE_CLASS_TEXT);
            if (existing != null) {
                JsonElement value = existing.get(field.key);
                if (value != null && !value.isJsonNull()) {
                    input.setText(value.getAsString());
                }
            }
            LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT);
            params.setMargins(0, 0, 0, pad / 2);
            layout.addView(input, params);
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
            JsonObject payload = new JsonObject();
            boolean valid = true;
            for (Map.Entry<Field, EditText> entry : inputs.entrySet()) {
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
                dialog.dismiss();
                listener.onSubmit(payload);
            }
        }));
        dialog.show();
    }
}
