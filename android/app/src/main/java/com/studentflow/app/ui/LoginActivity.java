package com.studentflow.app.ui;

import android.content.Intent;
import android.os.Bundle;
import android.widget.EditText;
import android.widget.TextView;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;

import com.google.android.material.button.MaterialButton;
import com.google.android.material.textfield.TextInputEditText;
import com.google.gson.JsonObject;
import com.studentflow.app.R;
import com.studentflow.app.api.ApiClient;
import com.studentflow.app.data.TokenStore;
import com.studentflow.app.models.LoginRequest;
import com.studentflow.app.models.LoginResponse;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class LoginActivity extends AppCompatActivity {
    private TextInputEditText usernameInput;
    private TextInputEditText passwordInput;
    private TextView message;
    private MaterialButton loginButton;
    private TokenStore tokenStore;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        tokenStore = new TokenStore(this);
        if (tokenStore.hasToken()) {
            openMain();
            return;
        }
        setContentView(R.layout.activity_login);
        usernameInput = findViewById(R.id.usernameInput);
        passwordInput = findViewById(R.id.passwordInput);
        message = findViewById(R.id.loginMessage);
        loginButton = findViewById(R.id.loginButton);
        MaterialButton googleLoginButton = findViewById(R.id.googleLoginButton);
        MaterialButton githubLoginButton = findViewById(R.id.githubLoginButton);
        loginButton.setOnClickListener(v -> login());
        googleLoginButton.setOnClickListener(v -> socialLogin("google"));
        githubLoginButton.setOnClickListener(v -> socialLogin("github"));
    }

    private void login() {
        String username = text(usernameInput);
        String password = text(passwordInput);
        if (username.isEmpty() || password.isEmpty()) {
            message.setText("Enter username and password.");
            return;
        }
        loginButton.setEnabled(false);
        message.setText("Signing in...");
        ApiClient.reset();
        ApiClient.service(this).login(new LoginRequest(username, password)).enqueue(new Callback<LoginResponse>() {
            @Override
            public void onResponse(Call<LoginResponse> call, Response<LoginResponse> response) {
                loginButton.setEnabled(true);
                LoginResponse body = response.body();
                if (response.isSuccessful() && body != null && body.token != null) {
                    saveAndOpen(body);
                    return;
                }
                message.setText("Login failed. Check credentials and API server.");
            }

            @Override
            public void onFailure(Call<LoginResponse> call, Throwable t) {
                loginButton.setEnabled(true);
                message.setText("Network error: " + t.getMessage());
            }
        });
    }

    private void socialLogin(String provider) {
        EditText input = new EditText(this);
        input.setSingleLine(false);
        input.setHint(provider.equals("google") ? "Paste Google ID token" : "Paste GitHub code or access token");
        new AlertDialog.Builder(this)
                .setTitle(provider.equals("google") ? "Student Google sign-in" : "Student GitHub sign-in")
                .setMessage(provider.equals("google")
                        ? "Backend verifies the Google ID token and links it to a student email."
                        : "Backend exchanges a GitHub code or verifies an access token and links it to a student email.")
                .setView(input)
                .setNegativeButton("Cancel", null)
                .setPositiveButton("Continue", (dialog, which) -> submitSocial(provider, input.getText().toString().trim()))
                .show();
    }

    private void submitSocial(String provider, String value) {
        if (value.isEmpty()) {
            message.setText("Missing " + provider + " token/code.");
            return;
        }
        JsonObject payload = new JsonObject();
        if (provider.equals("google")) {
            payload.addProperty("id_token", value);
        } else if (value.startsWith("gho_") || value.startsWith("github_pat_") || value.startsWith("test-github:")) {
            payload.addProperty("access_token", value);
        } else {
            payload.addProperty("code", value);
        }
        message.setText("Signing in with " + provider + "...");
        ApiClient.reset();
        (provider.equals("google")
                ? ApiClient.service(this).googleLogin(payload)
                : ApiClient.service(this).githubLogin(payload))
                .enqueue(new Callback<LoginResponse>() {
                    @Override
                    public void onResponse(Call<LoginResponse> call, Response<LoginResponse> response) {
                        LoginResponse body = response.body();
                        if (response.isSuccessful() && body != null && body.token != null) {
                            saveAndOpen(body);
                        } else {
                            message.setText("Social sign-in failed: HTTP " + response.code());
                        }
                    }

                    @Override
                    public void onFailure(Call<LoginResponse> call, Throwable t) {
                        message.setText("Network error: " + t.getMessage());
                    }
                });
    }

    private void saveAndOpen(LoginResponse body) {
        tokenStore.saveSession(body.token, body.user == null ? "{}" : body.user.toString());
        ApiClient.reset();
        openMain();
    }

    private String text(TextInputEditText input) {
        return input.getText() == null ? "" : input.getText().toString().trim();
    }

    private void openMain() {
        startActivity(new Intent(this, MainActivity.class));
        finish();
    }
}
