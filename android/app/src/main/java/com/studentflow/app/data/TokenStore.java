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

    public String getToken() {
        return preferences.getString(KEY_TOKEN, null);
    }

    public String getUserJson() {
        return preferences.getString(KEY_USER_JSON, null);
    }

    public void saveOauthState(String state) {
        preferences.edit().putString(KEY_OAUTH_STATE, state).apply();
    }

    public String consumeOauthState() {
        String state = preferences.getString(KEY_OAUTH_STATE, null);
        preferences.edit().remove(KEY_OAUTH_STATE).apply();
        return state;
    }

    public boolean hasToken() {
        String token = getToken();
        return token != null && !token.trim().isEmpty();
    }

    public void clear() {
        preferences.edit().clear().apply();
    }
}
