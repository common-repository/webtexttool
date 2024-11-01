<div id="wtt-account" ng-show="auth" class="wrap"
     cg-busy="{promise:accountPromise,message:promiseMessage,backdrop:true,templateUrl:promiseTemplate}" ng-cloak>

    <div id="poststuff">

        <div id="post-body" class="metabox-holder columns-2">

            <div id="post-body-content">

                <div class="postbox">

                    <h3 class="postbox-title">
                        <span><?php echo __(WTT_PLUGIN_NAME) ?> dashboard</span>
                        <img class="webtexttool-logo"
                             ng-src="{{logo}}" width="200"
                             alt="<?php echo __(WTT_PLUGIN_NAME) ?>"/>
                    </h3>

                    <div class="inside">

                        <h3 class="wtt_view_title">Thank you for using <?php echo __(WTT_PLUGIN_NAME) ?> to analyze, optimize and
                            monitor your content.</h3>
                        <hr class="wtt-hr">

                        <div id="wtt_dashboard_body">

                            <div id="wtt-plans">
                                <div class="inside account-summary">

                                    <div class="wtt-plan">
                                        <h3 class="wtt-plan-title">Account details for
                                            <strong>{{userInfo.UserName}}</strong>:</h3>
                                        <ul class="plan-details">
                                            <li class="plan-item">
                                                <span class="value-no">{{userInfo.SubscriptionName}}</span>
                                                <span class="value-label">Plan subscription</span>
                                            </li>
                                            <li class="plan-item" ng-show="userInfo.SubscriptionName !== 'Teammember'">
                                                <span class="value-no">{{userInfo.Credits}}</span>
                                                <span class="value-label">Keyword credits this month</span>
                                            </li>
                                            <li class="plan-item" ng-show="userInfo.SubscriptionName !== 'Teammember'">
                                                <span class="value-no">{{userInfo.AvailablePageTrackers}}</span>
                                                <span class="value-label">Pagetrackers available</span>
                                            </li>
                                            <li class="plan-item" ng-show="userInfo.UserState == 3">
                                                <span class="value-no">{{displayTrialDays(userInfo.TrialDays)}}</span>
                                                <span class="value-label">Remaining trial days</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="subscription-button" ng-show="userInfo.CanUpgrade && userInfo.SubscriptionName !== 'Teammember'">
                                        <a class="subscription-label subscription-label-orange"
                                           ng-href="{{WttAppUrl}}account/subscription"
                                           target="_blank">Upgrade your account</a>
                                    </div>
                                </div>
                            </div>

                            <hr class="wtt-hr">

                            <div class="account-options">
                                <ul class="fa-ul">
                                    <li><i class="fa-li fa fa-user"></i><a
                                                ng-href="{{WttAppUrl}}account"
                                                target="_blank">My Account</a></li>
                                    <li><i class="fa-li fa fa-check-square-o"></i><a
                                                ng-href="{{WttAppUrl}}account/subscription"
                                                target="_blank">Subscriptions</a></li>
                                    <li><i class="fa-li fa fa-key"></i><a
                                                ng-href="{{WttAppUrl}}account"
                                                target="_blank">Change your password</a></li>
                                    <li><i class="fa-li fa fa-exchange"></i><a
                                                ng-href="{{WttAppUrl}}account/transactions"
                                                target="_blank">Transactions overview</a></li>
                                </ul>
                            </div>

                            <hr class="wtt-hr">

                            <div id="wtt_settings_submit">
                                <button type="submit" class="button button-primary" ng-click="logout()"
                                        data-style="expand-left" ladda="loading">
                                    <span class="ladda-label">Sign out</span>
                                    <span class="ladda-spinner"></span>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="wtt_helpaccountside" class="wtt_helpside">
        <div>
            <span class="wtt_title">FAQs</span>
            <ul>
                <li>
                    <a ng-href="https://www.textmetrics.com/our-story"
                       target="_blank">What is <?php echo __(WTT_PLUGIN_NAME) ?>?</a></li>
                <li>
                    <a ng-href="https://www.textmetrics.com/knowledgebase/i-cant-reach-a-100-score-in-the-editor-how-do-i-fix-this"
                       target="_blank">Is my text 100% optimized if I use <?php echo __(WTT_PLUGIN_NAME) ?>?</a></li>
                <li>
                    <a ng-href="https://www.textmetrics.com/knowledgebase/how-can-textmetrics-help-me-improve-my-seo"
                       target="_blank">How can <?php echo __(WTT_PLUGIN_NAME) ?> help me improve my SEO?</a></li>
            </ul>
        </div>
    </div>
</div>