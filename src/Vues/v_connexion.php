<?php
/**
 * Vue Connexion (Bootstrap 5.3)
 */
?>
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="card-title h5 mb-0">Identification utilisateur</h3>
            </div>
            <div class="card-body">
                <form method="post"
                      action="index.php?uc=connexion&action=valideConnexion">
                    <fieldset>
                        <div class="mb-3">
                            <label for="login" class="form-label">Login</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input id="login"
                                       class="form-control"
                                       placeholder="Login"
                                       name="login"
                                       type="text"
                                       maxlength="45">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="mdp" class="form-label">Mot de passe</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input id="mdp"
                                       class="form-control"
                                       placeholder="Mot de passe"
                                       name="mdp"
                                       type="password"
                                       maxlength="45">
                            </div>
                        </div>
                        <button class="btn btn-success w-100" type="submit">
                            Se connecter
                        </button>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>