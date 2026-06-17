package com.studentflow.app.models;

import com.google.gson.annotations.SerializedName;

public class ChangePasswordRequest {
    @SerializedName("current_password")
    private final String currentPassword;
    @SerializedName("new_password")
    private final String newPassword;
    @SerializedName("new_password_confirmation")
    private final String newPasswordConfirmation;

    public ChangePasswordRequest(String currentPassword, String newPassword, String newPasswordConfirmation) {
        this.currentPassword = currentPassword;
        this.newPassword = newPassword;
        this.newPasswordConfirmation = newPasswordConfirmation;
    }
}
