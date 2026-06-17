package com.studentflow.app.ui;

import android.os.Bundle;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import com.google.android.material.button.MaterialButton;
import com.google.android.material.textfield.TextInputEditText;
import com.google.gson.JsonObject;
import com.studentflow.app.R;
import com.studentflow.app.api.ApiClient;
import com.studentflow.app.models.ChangePasswordRequest;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class ChangePasswordActivity extends AppCompatActivity {
    private TextInputEditText currentInput;
    private TextInputEditText newInput;
    private TextInputEditText confirmInput;
    private TextView message;
    private MaterialButton saveButton;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_change_password);
        currentInput = findViewById(R.id.currentPasswordInput);
        newInput = findViewById(R.id.newPasswordInput);
        confirmInput = findViewById(R.id.confirmPasswordInput);
        message = findViewById(R.id.passwordMessage);
        saveButton = findViewById(R.id.savePasswordButton);
        saveButton.setOnClickListener(v -> save());
    }

    private void save() {
        String current = text(currentInput);
        String next = text(newInput);
        String confirm = text(confirmInput);
        if (current.isEmpty() || next.isEmpty() || confirm.isEmpty()) {
            message.setText("All password fields are required.");
            return;
        }
        if (!next.equals(confirm)) {
            message.setText("New password confirmation does not match.");
            return;
        }
        saveButton.setEnabled(false);
        message.setText("Saving...");
        ApiClient.service(this).changePassword(new ChangePasswordRequest(current, next, confirm)).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                saveButton.setEnabled(true);
                if (response.isSuccessful()) {
                    message.setText("Password changed.");
                } else {
                    message.setText("Change failed: HTTP " + response.code());
                }
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                saveButton.setEnabled(true);
                message.setText("Network error: " + t.getMessage());
            }
        });
    }

    private String text(TextInputEditText input) {
        return input.getText() == null ? "" : input.getText().toString().trim();
    }
}
