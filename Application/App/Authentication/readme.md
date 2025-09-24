# Authentication process

Example:
- Account uses "system" identification, this implies
  - factor credentials
  - factor yubikey

Things to know:

- {MFA}
  - multifactor authentication 
  - it does not include unlocking device and unlocking app (see: {Device Factor})


- {Device Factor}
  - exists/happens sole on device
  - authentication used on device to unlock app/data 
    - fingerprint
    - face id


- {Authentication Token}
  - it is unique 
  - issued after the successful {MFA} process
  - has a long lifetime (maybe a month)
  - on timeout, {MFA} must be done again (except credentials)
  - used as the authentication key instead of {MFA}
  - used to request {Session Token} 


- {Access Token}
  - it is unique
  - issued after the successful {Authentication Token} process
  - has a short lifetime (maybe 15 minutes)
  - on timeout, {Authentication Token} must request a new {Access Token}
  - used to access resources (doing stuff going places)

## (A) First time app usage (selected through A.1 condition)

### 0. Install app ... ^.^
### 1. Open app

- Maybe {Device Factor} locking the app
- No {Authentication Token} available
- No {Access Token} available

### 2. Show Login

- The first factor is *always* "credentials"

1. Get the current state of the sign-in process for the user
   - Ask for username
     - Send it as
         - {credentialIdentifier}
     - Send it to
         - /app/authentication/process/sign-in
   - The answer will be 
     - an error if authentication is not available for that user (no app access enabled) [401]
     - the current MFA challenge (for that user) the login process will need to step through [200]
       - credentials
       - url


2. Solve the first MFA challenge (its credentials, remember?)
   - Ask for password
   - Send them as 
     - {credentialIdentifier} (from A.2.1)
     - {credentialPassword}
   - Send them to (url from answer of A.2.1)
     - /app/authentication/factor/credentials
   - The answer will be
     - an error if something's wrong about the credentials [400|403|422]
     - a success message containing either
       - the next MFA challenge (and account information)
         - yubikey
         - url
       - the {Authentication Token} (and account information) ... ^.^ *yeah!*


3. Solve the next MFA challenge
   - Ask for YubiKey
   - Send it as
     - {credentialKey} 
   - Send it to (url from answer of A.2.2)
     - /app/authentication/factor/yubikey
   - The answer will be
       - an error if something's wrong about the key [400|403|422]
       - a success message containing either
           - the next MFA challenge (and account information)
               - ... you know how it works by now ... ^.^
           - the {Authentication Token} (and account information) ... ^.^ *yeah!*


4. Using the {Authentication Token}
   - Require a {Device Factor} to protect the app (face id/fingerprint)
   - Save {Authentication Token} on the device "inside" app
   - Send it as
       - {credentialKey}
   - Send it to (url from answer of A.2.2)
       - /app/authentication/factor/token
   - The answer will be
       - an error if something's wrong about the key [400|403|422]
       - a success message containing
         - the {Access Key}

## (B) Next time app usage (selected through B.1 condition)

### 1. Open app

- Yes, {Device Factor} locking the app
- Yes, {Authentication Token} available
- Maybe {Access Token} available

### 2. Unlock app

- use {Device Factor}

### 3. Use app

1. Normal operation
   - [2xx] Works great
   - [400|403|404|...] Not so good
   - [401] Bad, go to authentication operation (B.3.2)
   - [5xx] ... -.-

2. Authentication operation:

   - [401] {Access Token} invalid
     - use {Authentication Token} to request new {Access Token}
       - [200] save new {Access Token}, back to normal operations (B.3.1)
       - [401] {Authentication Token} invalid
         - use {MFA} to request new {Authentication Token}
           - [200] save new {Authentication Token}, back to normal operations (B.3.1)
