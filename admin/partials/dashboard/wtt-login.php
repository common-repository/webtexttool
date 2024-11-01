<div id="webtexttool-login" ng-hide="auth" class="wrap" ng-cloak>

    <div id="poststuff">

        <div id="post-body" class="metabox-holder columns-2">

            <div id="post-body-content">

                <div class="postbox">

                    <h3 class="postbox-title">
                        <span><?php echo __(WTT_PLUGIN_NAME); ?> login</span>
                        <img class="webtexttool-logo" ng-src="{{logo}}" width="200"
                             alt="<?php echo __(WTT_PLUGIN_NAME); ?>"/>
                    </h3>

                    <div class="inside">
                        <p class="description">Use your <?php echo __(WTT_PLUGIN_NAME); ?> account credentials to log in the form below. <br/>
                            Please note
                            that these are not your WordPress username and password. <br/><br/>
                            If you don't have an account yet, you can easily create one for free <a href="https://textmetrics.com/try-textmetrics" target="_blank"><strong>here</strong></a>.</p>

                        <div ng-if="error != null" class="alert alert-danger" role="alert">
                            <strong>{{error}}</strong>
                        </div>

                        <form class="wtt-form" role="form" name="loginForm">
                            <table class="form-table">
                                <tr>
                                    <td>
                                        <label>E-mail:</label>
                                    </td>
                                    <td><input type="email" name="e-mail" class="regular-text"
                                               placeholder="E-mail"
                                               required="required" ng-model="loginModel.UserName"/>
                                    </td>
                                </tr>
                                <tr class="alternate">
                                    <td>
                                        <label>Password:</label>
                                    </td>
                                    <td><input type="password" name="password" class="regular-text"
                                               placeholder="Password" required="required"
                                               ng-model="loginModel.Password"/></td>
                                </tr>
                                <tr>
                                    <td>
                                        <button type="submit" class="button button-primary btn-signin" ng-click="login()"
                                                data-style="expand-left" ladda="loading">
                                            <span class="ladda-label">Sign in</span>
                                            <span class="ladda-spinner"></span>
                                        </button>
                                    </td>
                                    <td>
                                        <a id="forgot-password-link" ng-href="{{WttAppUrl}}forgot" target="_blank">I forgot my password</a>
                                    </td>
                                </tr>
                            </table>
                        </form>

                    </div>
                </div>

                <div class="postbox">
                    <div class="inside">
                        <p class="description">In order to generate an API Key, head over to <a ng-href="{{WttAppUrl}}account" target="_blank"><strong>My Account</strong></a> page and press "Generate API key".</p>
                        <p class="description"><strong>Note!</strong> This is only available in our Enterprise plan.</p>

                        <form class="wtt-form" role="form" name="apiKeyForm">
                            <table class="form-table">
                                <tr>
                                    <td>
                                        <label>API Key:</label>
                                    </td>
                                    <td><input type="text" name="apiKey" class="regular-text"
                                               placeholder="API Key"
                                               ng-model="apiKey.value"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <button type="submit" class="button button-primary btn-save" ng-click="saveApiKey()"
                                                data-style="expand-left" ladda="loading2">
                                            <span class="ladda-label">Save</span>
                                            <span class="ladda-spinner"></span>
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>