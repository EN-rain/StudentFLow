package com.studentflow.app.security;

import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.security.SecureRandom;

public final class Pkce {
    private static final char[] BASE64_URL = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_".toCharArray();
    private static final SecureRandom RANDOM = new SecureRandom();

    private Pkce() {
    }

    public static String generateVerifier() {
        byte[] bytes = new byte[32];
        RANDOM.nextBytes(bytes);
        return base64Url(bytes);
    }

    public static String challenge(String verifier) {
        try {
            MessageDigest digest = MessageDigest.getInstance("SHA-256");
            return base64Url(digest.digest(verifier.getBytes(StandardCharsets.US_ASCII)));
        } catch (NoSuchAlgorithmException e) {
            throw new IllegalStateException("SHA-256 is unavailable.", e);
        }
    }

    static String base64Url(byte[] input) {
        StringBuilder output = new StringBuilder((input.length * 4 + 2) / 3);
        int index = 0;
        while (index + 2 < input.length) {
            int value = ((input[index] & 0xff) << 16)
                    | ((input[index + 1] & 0xff) << 8)
                    | (input[index + 2] & 0xff);
            output.append(BASE64_URL[(value >>> 18) & 0x3f]);
            output.append(BASE64_URL[(value >>> 12) & 0x3f]);
            output.append(BASE64_URL[(value >>> 6) & 0x3f]);
            output.append(BASE64_URL[value & 0x3f]);
            index += 3;
        }

        int remaining = input.length - index;
        if (remaining == 1) {
            int value = (input[index] & 0xff) << 16;
            output.append(BASE64_URL[(value >>> 18) & 0x3f]);
            output.append(BASE64_URL[(value >>> 12) & 0x3f]);
        } else if (remaining == 2) {
            int value = ((input[index] & 0xff) << 16) | ((input[index + 1] & 0xff) << 8);
            output.append(BASE64_URL[(value >>> 18) & 0x3f]);
            output.append(BASE64_URL[(value >>> 12) & 0x3f]);
            output.append(BASE64_URL[(value >>> 6) & 0x3f]);
        }

        return output.toString();
    }
}
