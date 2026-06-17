package com.studentflow.app.ui;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import com.google.android.material.button.MaterialButton;
import com.google.android.material.textfield.TextInputEditText;
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
        loginButton.setOnClickListener(v -> login());
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
                    tokenStore.saveSession(body.token, body.user == null ? "{}" : body.user.toString());
                    ApiClient.reset();
                    openMain();
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

    private String text(TextInputEditText input) {
        return input.getText() == null ? "" : input.getText().toString().trim();
    }

    private void openMain() {
        startActivity(new Intent(this, MainActivity.class));
        finish();
    }
}
