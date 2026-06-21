package com.studentflow.app.security;

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertNotEquals;
import static org.junit.Assert.assertTrue;

import org.junit.Test;

public class PkceTest {
    @Test
    public void generatedVerifierUsesPkceSafeLengthAndCharacters() {
        String verifier = Pkce.generateVerifier();

        assertEquals(43, verifier.length());
        assertTrue(verifier.matches("^[A-Za-z0-9_-]+$"));
    }

    @Test
    public void generatedVerifiersAreNotRepeated() {
        assertNotEquals(Pkce.generateVerifier(), Pkce.generateVerifier());
    }

    @Test
    public void challengeMatchesRfc7636Example() {
        String verifier = "dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk";

        assertEquals("E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM", Pkce.challenge(verifier));
    }
}
