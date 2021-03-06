package User_Login.solution1;

import org.jetbrains.annotations.NotNull;

import java.time.LocalDateTime;
import java.util.Arrays;
import java.util.HashMap;

class LoginImpl implements Login {
    private final UserLogin userLogin;
    private final UserManager userManager;
    private final HashMap<String, User> tokenMap;

    private final HashMap<String, String> resetRequests = new HashMap<>();
    private LocalDateTime localDateTime;
    private PasswordManager passwordManager;

    LoginImpl(UserManager userManager, HashMap<String, User> tokenMap, UserLogin userLogin, LocalDateTime localDateTime, PasswordManager passwordManager) {
        this.tokenMap = tokenMap;
        this.userLogin = userLogin;
        this.userManager = userManager;
        this.localDateTime = localDateTime;
        this.passwordManager = passwordManager;
    }

    @Override
    public String login(String loginName, String password) throws Exception {
        if (!userManager.isUserRegistered(loginName))
            throw new UnregisteredUserException();
        if (!isConfirmedUser(loginName))
            throw new UnconfirmedUserException();
        if (passwordManager.isInvalidPassword(loginName, password))
            throw new PasswordInvalidException();

        User user = userManager.getUser(loginName);
        user.lastLoginDate = localDateTime;

        @NotNull String token = generateToken(loginName);
        tokenMap.put(token, user);

        return token;
    }

    @Override
    public boolean isLoginValid(String token) {
        LocalDateTime expirationDate = extractDateTime(token);
        @NotNull LocalDateTime now = localDateTime;
        if (now.isAfter(expirationDate)) {
            tokenMap.remove(token);
            return false;
        }
        return tokenMap.containsKey(token);
    }

    @Override
    public void requestPasswordReset(String email) {
        String key = generateResetRequestNumber(email);
        resetRequests.put(key, email);

        userLogin.sendPasswordResetEmail(key);
    }

    @Override
    public void resetPassword(String resetRequestNumber) {
        String email = resetRequests.remove(resetRequestNumber);
        User user = userManager.getUser(email);
        String password = userLogin.passwordManager.generatePassword(user);
        userLogin.passwordManager.savePassword(user, password);

        userLogin.sendNewPasswordEmail(user.email, password);
    }


    private boolean isConfirmedUser(String loginName) {
        return userManager.getUser(loginName).confirmed;
    }

    @NotNull
    private String generateToken(String loginName) {
        User user = userManager.getUser(loginName);
        LocalDateTime expirationDate = localDateTime.plusDays(1);

        String userdata = user.id.hashCode() + "" +
                user.email.hashCode() + "" +
                user.nickname.hashCode() + "" +
                user.lastLoginDate.hashCode() + "" +
                user.lastUpdatedDate.hashCode() + "";
        return
                Arrays.toString(Encryption.encryptPassword(userdata)).hashCode() +
                        "!" + expirationDate;
    }

    private LocalDateTime extractDateTime(String token) {
        return LocalDateTime.parse(token.split("!")[1]);
    }

    private String generateResetRequestNumber(String email) {
        //TODO
        return email;
    }

}