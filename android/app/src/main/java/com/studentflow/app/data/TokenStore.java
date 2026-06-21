package com.studentflow.app.data;

import android.content.Context;
import android.content.SharedPreferences;

import androidx.security.crypto.EncryptedSharedPreferences;
import androidx.security.crypto.MasterKey;

import java.io.IOException;
import java.security.GeneralSecurityException;

public class TokenStore {
    private static final String PREFS = "studentflow_auth";
    private static final String KEY_TOKEN = "api_token";
    private static final String KEY_USER_JSON = "user_json";
    private static final String KEY_OAUTH_STATE = "oauth_state";
    private static final String KEY_OAUTH_CODE_VERIFIER = "oauth_code_verifier";
    private static final String KEY_REMEMBER_ME = "remember_me";
    private static final String KEY_REMEMBERED_USERNAME = "remembered_username";

    private final SharedPreferences preferences;

    public TokenStore(Context context) {
        Context appContext = context.getApplicationContext();
        try {
            MasterKey masterKey = new MasterKey.Builder(appContext)
                    .setKeyScheme(MasterKey.KeyScheme.AES256_GCM)
                    .build();
            preferences = EncryptedSharedPreferences.create(
                    appContext,
                    PREFS,
                    masterKey,
                    EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
                    EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
            );
        } catch (GeneralSecurityException | IOException e) {
            throw new IllegalStateException("Unable to initialize secure session storage.", e);
        }
    }

    public void saveSession(String token, String userJson) {
        preferences.edit()
                .putString(KEY_TOKEN, token)
                .putString(KEY_USER_JSON, userJson)
                .apply();
    }

    public void saveRememberedLogin(boolean remember, String username) {
        SharedPreferences.Editor editor = preferences.edit().putBoolean(KEY_REMEMBER_ME, remember);
        if (remember) {
            editor.putString(KEY_REMEMBERED_USERNAME, username);
        } else {
            editor.remove(KEY_REMEMBERED_USERNAME);
        }
        editor.apply();
    }

    public boolean shouldRememberLogin() {
        return preferences.getBoolean(KEY_REMEMBER_ME, false);
    }

    public String getRememberedUsername() {
        return preferences.getString(KEY_REMEMBERED_USERNAME, "");
    }

    public String getToken() {
        return preferences.getString(KEY_TOKEN, null);
    }

    public String getUserJson() {
        return preferences.getString(KEY_USER_JSON, null);
    }

    public void saveOauthRequest(String state, String codeVerifier) {
        preferences.edit()
                .putString(KEY_OAUTH_STATE, state)
                .putString(KEY_OAUTH_CODE_VERIFIER, codeVerifier)
                .apply();
    }

    public String getOauthState() {
        return preferences.getString(KEY_OAUTH_STATE, null);
    }

    public String getOauthCodeVerifier() {
        return preferences.getString(KEY_OAUTH_CODE_VERIFIER, null);
    }

    public void clearOauthRequest() {
        preferences.edit()
                .remove(KEY_OAUTH_STATE)
                .remove(KEY_OAUTH_CODE_VERIFIER)
                .apply();
    }

    public boolean hasToken() {
        String token = getToken();
        return token != null && !token.trim().isEmpty();
    }

    public void clear() {
        boolean remember = shouldRememberLogin();
        String rememberedUsername = getRememberedUsername();
        SharedPreferences.Editor editor = preferences.edit().clear();
        if (remember) {
            editor.putBoolean(KEY_REMEMBER_ME, true);
            editor.putString(KEY_REMEMBERED_USERNAME, rememberedUsername);
        }
        editor.apply();
    }
}
