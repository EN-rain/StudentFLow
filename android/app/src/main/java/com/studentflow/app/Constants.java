package com.studentflow.app;

public final class Constants {
    public static final String API_BASE_URL = "https://studentflow-rbog.onrender.com/api/";
    /*
     * GoogleSignIn requestIdToken requires the Web OAuth client ID.
     * Keep the Android OAuth client in Google Console with package com.studentflow.app
     * and the app signing SHA-1, but do not put the Android client ID here.
     */
    public static final String GOOGLE_WEB_CLIENT_ID = "919040220334-psvoce66g4mcim0csum12mujhlmqk6oe.apps.googleusercontent.com";
    public static final String GITHUB_CLIENT_ID = "Ov23lipHaQtpSjuQyWmi";
    public static final String GITHUB_REDIRECT_URI = "studentflow://oauth/github";

    private Constants() {
    }
}
