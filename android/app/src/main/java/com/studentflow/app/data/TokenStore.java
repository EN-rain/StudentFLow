package com.studentflow.app.data;

import android.content.Context;
import android.content.SharedPreferences;

public class TokenStore {
    private static final String PREFS = "studentflow_auth";
    private static final String KEY_TOKEN = "api_token";
    private static final String KEY_USER_JSON = "user_json";

    private final SharedPreferences preferences;

    public TokenStore(Context context) {
        preferences = context.getApplicationContext().getSharedPreferences(PREFS, Context.MODE_PRIVATE);
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

    public boolean hasToken() {
        String token = getToken();
        return token != null && !token.trim().isEmpty();
    }

    public void clear() {
        preferences.edit().clear().apply();
    }
}
