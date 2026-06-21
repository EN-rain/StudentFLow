package com.studentflow.app.ui;

import android.animation.LayoutTransition;
import android.content.Intent;
import android.content.res.ColorStateList;
import android.net.Uri;
import android.os.Bundle;
import android.view.MotionEvent;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;
import android.widget.CheckBox;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;
import androidx.browser.customtabs.CustomTabsIntent;

import com.google.android.material.button.MaterialButton;
import com.google.android.material.snackbar.Snackbar;
import com.google.android.material.textfield.TextInputLayout;
import com.google.android.material.textfield.TextInputEditText;
import com.google.android.gms.auth.api.signin.GoogleSignIn;
import com.google.android.gms.auth.api.signin.GoogleSignInAccount;
import com.google.android.gms.auth.api.signin.GoogleSignInClient;
import com.google.android.gms.auth.api.signin.GoogleSignInOptions;
import com.google.android.gms.common.api.ApiException;
import com.google.android.gms.tasks.Task;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import com.studentflow.app.Constants;
import com.studentflow.app.R;
import com.studentflow.app.api.ApiClient;
import com.studentflow.app.data.TokenStore;
import com.studentflow.app.models.LoginResponse;
import com.studentflow.app.security.Pkce;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class LoginActivity extends AppCompatActivity {
    private static final int GOOGLE_SIGN_IN_REQUEST = 4100;
    private enum Mode { LOGIN, REGISTER, FORGOT }

    private Mode mode = Mode.LOGIN;
    private TextInputLayout nameLayout;
    private TextInputLayout usernameLayout;
    private TextInputLayout passwordLayout;
    private TextInputLayout confirmPasswordLayout;
    private TextInputEditText usernameInput;
    private TextInputEditText nameInput;
    private TextInputEditText passwordInput;
    private TextInputEditText confirmPasswordInput;
    private TextView forgotPasswordButton;
    private MaterialButton loginButton;
    private MaterialButton showLoginButton;
    private MaterialButton showRegisterButton;
    private ImageButton googleLoginButton;
    private ImageButton githubLoginButton;
    private CheckBox rememberMeCheckbox;
    private LinearLayout socialRow;
    private TokenStore tokenStore;
    private GoogleSignInClient googleSignInClient;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        tokenStore = new TokenStore(this);
        if (tokenStore.hasToken()) {
            openMain();
            return;
        }
        setContentView(R.layout.activity_login);
        nameLayout = findViewById(R.id.nameLayout);
        usernameLayout = findViewById(R.id.usernameLayout);
        passwordLayout = findViewById(R.id.passwordLayout);
        confirmPasswordLayout = findViewById(R.id.confirmPasswordLayout);
        nameInput = findViewById(R.id.nameInput);
        usernameInput = findViewById(R.id.usernameInput);
        passwordInput = findViewById(R.id.passwordInput);
        confirmPasswordInput = findViewById(R.id.confirmPasswordInput);
        forgotPasswordButton = findViewById(R.id.forgotPasswordButton);
        loginButton = findViewById(R.id.loginButton);
        showLoginButton = findViewById(R.id.showLoginButton);
        showRegisterButton = findViewById(R.id.showRegisterButton);
        socialRow = findViewById(R.id.socialRow);
        configureLayoutMotion();
        googleLoginButton = findViewById(R.id.googleLoginButton);
        githubLoginButton = findViewById(R.id.githubLoginButton);
        rememberMeCheckbox = findViewById(R.id.rememberMeCheckbox);
        rememberMeCheckbox.setChecked(tokenStore.shouldRememberLogin());
        usernameInput.setText(tokenStore.getRememberedUsername());
        googleSignInClient = GoogleSignIn.getClient(this, new GoogleSignInOptions.Builder(GoogleSignInOptions.DEFAULT_SIGN_IN)
                .requestEmail()
                .requestIdToken(Constants.GOOGLE_WEB_CLIENT_ID)
                .build());
        loginButton.setOnClickListener(v -> submitPrimary());
        showLoginButton.setOnClickListener(v -> setMode(Mode.LOGIN));
        showRegisterButton.setOnClickListener(v -> setMode(Mode.REGISTER));
        forgotPasswordButton.setOnClickListener(v -> setMode(mode == Mode.FORGOT ? Mode.LOGIN : Mode.FORGOT));
        googleLoginButton.setOnClickListener(v -> startGoogleLogin());
        githubLoginButton.setOnClickListener(v -> startGithubLogin());
        addPressMotion(loginButton);
        addPressMotion(showLoginButton);
        addPressMotion(showRegisterButton);
        addPressMotion(forgotPasswordButton);
        addPressMotion(googleLoginButton);
        addPressMotion(githubLoginButton);
        setMode(Mode.LOGIN);
        handleDeepLink(getIntent());
    }

    private void setMode(Mode nextMode) {
        mode = nextMode;
        nameLayout.setVisibility(mode == Mode.REGISTER ? View.VISIBLE : View.GONE);
        passwordLayout.setVisibility(mode == Mode.FORGOT ? View.GONE : View.VISIBLE);
        confirmPasswordLayout.setVisibility(mode == Mode.REGISTER ? View.VISIBLE : View.GONE);
        socialRow.setVisibility(mode == Mode.FORGOT ? View.GONE : View.VISIBLE);
        rememberMeCheckbox.setVisibility(mode == Mode.LOGIN ? View.VISIBLE : View.GONE);
        usernameLayout.setHint(mode == Mode.LOGIN ? "Username or email" : "Email");
        loginButton.setText(primaryActionText());
        forgotPasswordButton.setText(mode == Mode.FORGOT ? "Back to login" : "Forgot password?");
        showLoginButton.setEnabled(true);
        showRegisterButton.setEnabled(true);
        showLoginButton.setAlpha(mode == Mode.LOGIN ? 1.0f : 0.72f);
        showRegisterButton.setAlpha(mode == Mode.REGISTER ? 1.0f : 0.72f);
        applyToggleState(showLoginButton, mode == Mode.LOGIN);
        applyToggleState(showRegisterButton, mode == Mode.REGISTER);
    }

    private void applyToggleState(MaterialButton button, boolean selected) {
        int primary = getColor(R.color.studentflow_primary);
        int field = getColor(R.color.studentflow_field);
        button.setBackgroundTintList(ColorStateList.valueOf(selected ? primary : field));
        button.setTextColor(selected ? field : primary);
    }

    private void configureLayoutMotion() {
        ViewGroup authPanel = findViewById(R.id.authPanel);
        LayoutTransition transition = new LayoutTransition();
        transition.enableTransitionType(LayoutTransition.CHANGING);
        transition.setDuration(LayoutTransition.APPEARING, 220);
        transition.setDuration(LayoutTransition.DISAPPEARING, 140);
        transition.setDuration(LayoutTransition.CHANGE_APPEARING, 220);
        transition.setDuration(LayoutTransition.CHANGE_DISAPPEARING, 160);
        authPanel.setLayoutTransition(transition);
    }

    private void addPressMotion(View view) {
        view.setOnTouchListener((target, event) -> {
            if (!target.isEnabled()) {
                return false;
            }
            if (event.getActionMasked() == MotionEvent.ACTION_DOWN) {
                target.animate().scaleX(0.96f).scaleY(0.96f).alpha(0.88f).setDuration(90).start();
            } else if (event.getActionMasked() == MotionEvent.ACTION_UP
                    || event.getActionMasked() == MotionEvent.ACTION_CANCEL) {
                target.animate().scaleX(1f).scaleY(1f).alpha(1f).setDuration(140).start();
            }
            return false;
        });
    }

    private void showError(String text) {
        showPopup(text, true);
        View panel = findViewById(R.id.authPanel);
        panel.animate()
                .translationX(12f)
                .setDuration(55)
                .withEndAction(() -> panel.animate()
                        .translationX(-12f)
                        .setDuration(55)
                        .withEndAction(() -> panel.animate().translationX(0f).setDuration(70).start())
                        .start())
                .start();
    }

    private void showStatus(String text) {
        showPopup(text, false);
    }

    private void showPopup(String text, boolean error) {
        Snackbar snackbar = Snackbar.make(findViewById(android.R.id.content), text, Snackbar.LENGTH_LONG);
        snackbar.setBackgroundTint(getColor(error ? R.color.studentflow_error : R.color.studentflow_primary));
        snackbar.setTextColor(getColor(android.R.color.white));
        snackbar.show();
    }

    private void setAuthControlsEnabled(boolean enabled) {
        setAuthControlsEnabled(enabled, null);
    }

    private void setAuthControlsEnabled(boolean enabled, String busyText) {
        nameInput.setEnabled(enabled);
        usernameInput.setEnabled(enabled);
        passwordInput.setEnabled(enabled);
        confirmPasswordInput.setEnabled(enabled);
        nameLayout.setEnabled(enabled);
        usernameLayout.setEnabled(enabled);
        passwordLayout.setEnabled(enabled);
        confirmPasswordLayout.setEnabled(enabled);
        loginButton.setEnabled(enabled);
        showLoginButton.setEnabled(enabled);
        showRegisterButton.setEnabled(enabled);
        forgotPasswordButton.setEnabled(enabled);
        rememberMeCheckbox.setEnabled(enabled);
        googleLoginButton.setEnabled(enabled);
        githubLoginButton.setEnabled(enabled);
        socialRow.setAlpha(enabled ? 1f : 0.5f);
        loginButton.animate().alpha(enabled ? 1f : 0.78f).setDuration(140).start();
        loginButton.setText(enabled ? primaryActionText() : busyText == null ? "Working..." : busyText);
    }

    private String primaryActionText() {
        if (mode == Mode.REGISTER) {
            return "Register";
        }
        if (mode == Mode.FORGOT) {
            return "Send reset link";
        }
        return "Login";
    }

    private void submitPrimary() {
        if (mode == Mode.REGISTER) {
            register();
        } else if (mode == Mode.FORGOT) {
            forgotPassword();
        } else {
            login();
        }
    }

    private void login() {
        String username = text(usernameInput);
        String password = text(passwordInput);
        if (username.isEmpty() || password.isEmpty()) {
            showError("Enter username and password.");
            return;
        }
        setAuthControlsEnabled(false, "Signing in...");
        tokenStore.saveRememberedLogin(rememberMeCheckbox.isChecked(), username);
        ApiClient.reset();
        ApiClient.service(this).login(username, password).enqueue(new Callback<LoginResponse>() {
            @Override
            public void onResponse(Call<LoginResponse> call, Response<LoginResponse> response) {
                setAuthControlsEnabled(true);
                LoginResponse body = response.body();
                if (response.isSuccessful() && body != null && body.token != null) {
                    saveAndOpen(body);
                    return;
                }
                showError("Login failed. " + errorMessage(response));
            }

            @Override
            public void onFailure(Call<LoginResponse> call, Throwable t) {
                setAuthControlsEnabled(true);
                showError("Connection problem. Please try again.");
            }
        });
    }

    private void register() {
        String name = text(nameInput);
        String email = text(usernameInput);
        String password = text(passwordInput);
        String confirm = text(confirmPasswordInput);
        if (name.isEmpty() || email.isEmpty() || password.isEmpty() || confirm.isEmpty()) {
            showError("Enter name, email, password, and confirmation.");
            return;
        }
        setAuthControlsEnabled(false, "Registering...");
        ApiClient.reset();
        ApiClient.service(this).register(name, email, password, confirm).enqueue(new Callback<LoginResponse>() {
            @Override
            public void onResponse(Call<LoginResponse> call, Response<LoginResponse> response) {
                setAuthControlsEnabled(true);
                LoginResponse body = response.body();
                if (response.isSuccessful() && body != null && body.token != null) {
                    saveAndOpen(body);
                    return;
                }
                showError("Registration failed. " + errorMessage(response));
            }

            @Override
            public void onFailure(Call<LoginResponse> call, Throwable t) {
                setAuthControlsEnabled(true);
                showError("Connection problem. Please try again.");
            }
        });
    }

    private void forgotPassword() {
        String email = text(usernameInput);
        if (email.isEmpty()) {
            showError("Enter your email.");
            return;
        }
        setAuthControlsEnabled(false, "Sending reset link...");
        ApiClient.reset();
        ApiClient.service(this).forgotPassword(email).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                setAuthControlsEnabled(true);
                if (response.isSuccessful() && response.body() != null && response.body().has("message")) {
                    showStatus(response.body().get("message").getAsString());
                    return;
                }
                showError("Reset failed. " + errorMessage(response));
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                setAuthControlsEnabled(true);
                showError("Connection problem. Please try again.");
            }
        });
    }

    private void startGoogleLogin() {
        setAuthControlsEnabled(false, "Opening Google...");
        googleSignInClient.signOut().addOnCompleteListener(task ->
                startActivityForResult(googleSignInClient.getSignInIntent(), GOOGLE_SIGN_IN_REQUEST));
        setAuthControlsEnabled(true);
    }

    private void startGithubLogin() {
        String state = Pkce.generateVerifier();
        String codeVerifier = Pkce.generateVerifier();
        String codeChallenge = Pkce.challenge(codeVerifier);
        tokenStore.saveOauthRequest(state, codeVerifier);

        JsonObject payload = new JsonObject();
        payload.addProperty("state", state);
        payload.addProperty("code_challenge", codeChallenge);

        setAuthControlsEnabled(false, "Opening GitHub...");
        ApiClient.service(this).githubMobileStart(payload).enqueue(new Callback<JsonObject>() {
            @Override
            public void onResponse(Call<JsonObject> call, Response<JsonObject> response) {
                if (!response.isSuccessful() || response.body() == null || !response.body().has("authorization_url")) {
                    tokenStore.clearOauthRequest();
                    setAuthControlsEnabled(true);
                    showError("GitHub sign-in failed. " + errorMessage(response));
                    return;
                }

                Uri uri = Uri.parse(response.body().get("authorization_url").getAsString());
                new CustomTabsIntent.Builder().build().launchUrl(LoginActivity.this, uri);
                setAuthControlsEnabled(true);
            }

            @Override
            public void onFailure(Call<JsonObject> call, Throwable t) {
                tokenStore.clearOauthRequest();
                setAuthControlsEnabled(true);
                showError("Connection problem. Please try again.");
            }
        });
    }

    @Override
    protected void onNewIntent(Intent intent) {
        super.onNewIntent(intent);
        setIntent(intent);
        handleDeepLink(intent);
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode != GOOGLE_SIGN_IN_REQUEST) {
            return;
        }
        Task<GoogleSignInAccount> task = GoogleSignIn.getSignedInAccountFromIntent(data);
        try {
            GoogleSignInAccount account = task.getResult(ApiException.class);
            String idToken = account == null ? null : account.getIdToken();
            if (idToken == null || idToken.trim().isEmpty()) {
                showError("Google sign-in could not be completed. Please try again.");
                return;
            }
            submitSocial("google", idToken);
        } catch (ApiException e) {
            showError("Google sign-in failed. Please try again.");
        }
    }

    private void handleDeepLink(Intent intent) {
        Uri uri = intent == null ? null : intent.getData();
        Uri expectedBase = Uri.parse(Constants.WEB_BASE_URL);
        if (uri == null
                || !"https".equalsIgnoreCase(uri.getScheme())
                || !expectedBase.getHost().equalsIgnoreCase(uri.getHost())
                || !"/mobile/oauth/github".equals(uri.getPath())) {
            return;
        }

        String error = uri.getQueryParameter("error_description");
        if (error == null) {
            error = uri.getQueryParameter("error");
        }
        if (error != null && !error.trim().isEmpty()) {
            tokenStore.clearOauthRequest();
            showError("GitHub sign-in failed. Please try again.");
            return;
        }

        String returnedState = uri.getQueryParameter("state");
        String expectedState = tokenStore.getOauthState();
        String codeVerifier = tokenStore.getOauthCodeVerifier();
        if (expectedState == null || returnedState == null || !expectedState.equals(returnedState) || codeVerifier == null) {
            tokenStore.clearOauthRequest();
            showError("GitHub sign-in could not be verified. Please try again.");
            return;
        }

        String code = uri.getQueryParameter("code");
        if (code == null || code.trim().isEmpty()) {
            tokenStore.clearOauthRequest();
            showError("GitHub sign-in could not be completed. Please try again.");
            return;
        }

        JsonObject payload = new JsonObject();
        payload.addProperty("code", code);
        payload.addProperty("state", returnedState);
        payload.addProperty("code_verifier", codeVerifier);
        tokenStore.clearOauthRequest();

        setAuthControlsEnabled(false);
        ApiClient.service(this).githubMobileComplete(payload).enqueue(new Callback<LoginResponse>() {
            @Override
            public void onResponse(Call<LoginResponse> call, Response<LoginResponse> response) {
                LoginResponse body = response.body();
                if (response.isSuccessful() && body != null && body.token != null) {
                    saveAndOpen(body);
                } else {
                    setAuthControlsEnabled(true);
                    showError("GitHub sign-in failed. " + errorMessage(response));
                }
            }

            @Override
            public void onFailure(Call<LoginResponse> call, Throwable t) {
                setAuthControlsEnabled(true);
                showError("Connection problem. Please try again.");
            }
        });
    }

    private void submitSocial(String provider, String value) {
        submitSocial(provider, value, null);
    }

    private void submitSocial(String provider, String value, String redirectUri) {
        if (value.isEmpty()) {
            showError("Could not complete sign-in. Please try again.");
            return;
        }
        JsonObject payload = new JsonObject();
        if (provider.equals("google")) {
            payload.addProperty("id_token", value);
        } else if (value.startsWith("gho_") || value.startsWith("github_pat_") || value.startsWith("test-github:")) {
            payload.addProperty("access_token", value);
        } else {
            payload.addProperty("code", value);
            if (redirectUri != null && !redirectUri.trim().isEmpty()) {
                payload.addProperty("redirect_uri", redirectUri);
            }
        }
        setAuthControlsEnabled(false, "Signing in...");
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
                            setAuthControlsEnabled(true);
                            showError("Sign-in failed. " + errorMessage(response));
                        }
                    }

                    @Override
                    public void onFailure(Call<LoginResponse> call, Throwable t) {
                        setAuthControlsEnabled(true);
                        showError("Connection problem. Please try again.");
                    }
                });
    }

    private void saveAndOpen(LoginResponse body) {
        if (body.user != null && body.user.has("role") && "admin".equals(body.user.get("role").getAsString())) {
            tokenStore.clear();
            StudentEndpointFragment.clearCache();
            setAuthControlsEnabled(true);
            showError("Admin accounts use the web dashboard.");
            return;
        }
        StudentEndpointFragment.clearCache();
        tokenStore.saveSession(body.token, body.user == null ? "{}" : body.user.toString());
        ApiClient.reset();
        openMain();
    }

    private String errorMessage(Response<?> response) {
        String fallback = "Please try again.";
        try {
            if (response.errorBody() == null) {
                return fallback;
            }
            String raw = response.errorBody().string();
            JsonObject json = JsonParser.parseString(raw).getAsJsonObject();
            if (json.has("message") && !json.get("message").isJsonNull()) {
                return json.get("message").getAsString();
            }
            if (json.has("errors") && json.get("errors").isJsonObject()) {
                JsonObject errors = json.getAsJsonObject("errors");
                for (String key : errors.keySet()) {
                    if (errors.get(key).isJsonArray() && errors.getAsJsonArray(key).size() > 0) {
                        return errors.getAsJsonArray(key).get(0).getAsString();
                    }
                }
            }
        } catch (Exception ignored) {
            return fallback;
        }
        return fallback;
    }

    private String text(TextInputEditText input) {
        return input.getText() == null ? "" : input.getText().toString().trim();
    }

    private void openMain() {
        startActivity(new Intent(this, MainActivity.class));
        overridePendingTransition(R.anim.sf_enter, R.anim.sf_exit);
        finish();
    }
}
