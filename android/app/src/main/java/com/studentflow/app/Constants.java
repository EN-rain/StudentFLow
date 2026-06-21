package com.studentflow.app;

public final class Constants {
    public static final String WEB_BASE_URL = "https://studentflow-rbog.onrender.com";
    public static final String API_BASE_URL = WEB_BASE_URL + "/api/";
    /*
     * GoogleSignIn requestIdToken requires the Web OAuth client ID.
     * Keep the Android OAuth client in Google Console with package com.studentflow.app
     * and the app signing SHA-1, but do not put the Android client ID here.
     */
    public static final String GOOGLE_WEB_CLIENT_ID = BuildConfig.GOOGLE_WEB_CLIENT_ID;

    private Constants() {
    }
}
