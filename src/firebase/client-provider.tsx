'use client';

import React, { useMemo, type ReactNode } from 'react';
import { FirebaseProvider } from '@/firebase/provider';
import { initializeApp, getApps, type FirebaseApp } from 'firebase/app';
import { getAuth, type Auth } from 'firebase/auth';
import { getFirestore, type Firestore } from 'firebase/firestore';
import { firebaseConfig } from './config';

interface FirebaseClientProviderProps {
  children: ReactNode;
}

// This function is safe to be called on the server because it doesn't
// depend on browser-specific APIs. However, we only call it inside
// the client provider to ensure SDKs are used correctly.
function getFirebaseServices() {
  let app: FirebaseApp;
  if (!getApps().length) {
    app = initializeApp(firebaseConfig);
  } else {
    app = getApps()[0];
  }
  
  return {
    firebaseApp: app,
    auth: getAuth(app),
    firestore: getFirestore(app),
  };
}

export function FirebaseClientProvider({ children }: FirebaseClientProviderProps) {
  // By calling and memoizing the initialization function here,
  // we ensure that Firebase is only initialized once, and only on the client.
  const firebaseServices = useMemo(() => {
    return getFirebaseServices();
  }, []);

  return (
    <FirebaseProvider
      firebaseApp={firebaseServices.firebaseApp}
      auth={firebaseServices.auth}
      firestore={firebaseServices.firestore}
    >
      {children}
    </FirebaseProvider>
  );
}