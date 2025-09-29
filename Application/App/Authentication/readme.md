# Authentication process

Example:
- Account uses "system" identification, this implies
  - factor credentials
  - factor yubikey

Things to know:

- {MFA}
  - multifactor authentication 
  - it does not include unlocking device and unlocking app (see: {Device Lock})


- {Credential Identifier}
  - the account identifier
    - username


- {Device Factor}
    - a hardware bound identifier
        - IMEI
        - HWID


- {Device Lock}
  - exists/happens sole on device
  - authentication used on device to unlock app/data 
    - fingerprint
    - face id


- {Authentication Token}
  - it is unique and a JWT
  - issued after the successful {MFA} process
  - has a long lifetime (maybe a month)
  - on timeout, {MFA} must be done again (except credentials)
  - used as the authentication key instead of {MFA}
  - used to request {Session Token} 


- {Access Token}
  - it is unique and a JWT
  - issued after the successful {Authentication Token} process
  - has a short lifetime (maybe 15 minutes)
  - on timeout, {Authentication Token} must request a new {Access Token}
  - used to access resources (doing stuff going places)


- {PHP Session}
  - works INDEPENDENT of this authentication
  - MUST BE created to tell SSW what account/data to use
  - it is unique
  - issued as an {Access Token} is requested
  - is reissued/renewed if no {PHP Session} but valid {Access Token} is presented


- HTTP Codes
  - [401] Unauthorized
    - Not signed in
    - {MFA} step invalid
    - {Authentication Token} invalid
    - {Access Token} invalid
  - [200]
    - Factor solved successfully with next {MFA} step
  - [201|200]
    - {Authentication Token} created|valid
    - {Access Token} created|valid
  - [400|422]
    - Request is wrong

## (A) First time app usage (selected through A.1 condition)

### 0. Install app ... ^.^
### 1. Open app

- Maybe {Device Lock} locking the app
- Maybe {Credential Identifier} is available
- No {Authentication Token} available
- No {Access Token} available

### 2. Show Login

- The first factor is *always* "credentials"

1. Get the current state of the sign-in process for the user
   - Ask for username/email/...
     - Send it as
         - {credentialDevice} ({Device Factor})
         - {credentialIdentifier} ({Credential Identifier})
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
     - {credentialDevice} ({Device Factor})
     - {credentialIdentifier} ({Credential Identifier})
     - {credentialPassword}
   - Send them to (url from answer of A.2.1)
     - /app/authentication/factor/credentials
   - The answer will be
     - an error if something's wrong about the credentials [400|401|422]
     - a success message containing either
       - the next MFA challenge (and account information)
         - yubikey
         - url
       - the {Authentication Token} (and account information) ... ^.^ *yeah!*


3. Solve the next MFA challenge
   - Ask for YubiKey
   - Send it as
     - {credentialDevice} ({Device Factor})
     - {credentialKey} 
   - Send it to (url from answer of A.2.2)
     - /app/authentication/factor/yubikey
   - The answer will be
       - an error if something's wrong about the key [400|401|422]
       - a success message containing either
           - the next MFA challenge (and account information)
               - ... you know how it works by now ... ^.^
           - the {Authentication Token} (and account information) ... ^.^ *yeah!*


4. Using the {Authentication Token}
   - Require a {Device Lock} to protect the app (face id/fingerprint)
   - Save {Authentication Token} on the device "inside" app
   - Send it as
       - {credentialDevice} ({Device Factor})
       - {credentialKey}
   - Send it to (url from answer of A.2.2)
       - /app/authentication/process/access
   - The answer will be
       - an error if something's wrong about the key [400|401|422]
       - a success message containing
         - the {Access Key}


5. Create/Impersonate {PHP Session}
   - Create/Impersonate session in SSW DB where {Authentication Token} is pointed to

### 3. Use app

- see C.3

## (B) Closing App / Locking Device

### 2. Lock app

- use {Device Lock} next time on open (C.2)


## (C) Next time app usage (selected through C.1 condition)

### 1. Open app

- Yes, {Device Lock} locking the app
- Yes, {Credential Identifier} is available
- Yes, {Authentication Token} is available
- Maybe {Access Token} is available

### 2. Unlock app

- use {Device Lock}

### 3. Use app

1. Normal operation
   - [2xx] Works great
   - [400|403|404|...] Not so good
   - [401] Bad, go to authentication operation (C.3.2)
   - [5xx] ... -.-

2. Authentication operation:

   - [401] {Access Token} invalid/missing
     - use {Authentication Token} to request new {Access Token}
       - [200] save new {Access Token}, back to normal operations (C.3.1)
       - [401] {Authentication Token} invalid/missing
         - use {MFA} to request new {Authentication Token} (A.2.2)
           - [200] save new {Authentication Token}, back to normal operations (C.3.1)

## (D) Logout of App

### 1. Open app

- Yes, {Device Lock} locking the app
- Yes, {Authentication Token} available
- Maybe {Access Token} available

### 2. Unlock app

- use {Device Lock}

### 3. Logout from app

1. Tell the current state of the sign-in process to remove *all* solved challenges
    - {Authentication Token}
        - Send it as
            - {credentialDevice}
            - {credentialKey}
        - Send it to
            - /app/authentication/process/sign-out
    - The answer will be
        - an error if authentication is not available for that user (no app access enabled) [401]


2. Remove {PHP Session}
    - Remove session from SSW DB where {Authentication Token} is pointed to


---
# App Things

## (I) Authentication data to save/have

- {Device Factor}
- {Credential Identifier}
- {Authentication Token}
- {Access Token}

## (II) Flow

1. Try to request something
   - [2xx] fine
   - [400] blame developer
   - [401] go to A.2.1 {MFA} sign-in
   - [403] won't happen, should have known
   - [404] no data
   - [422] blame user
   - [5xx] bad


2. do something with the user


3. go to II.1.
