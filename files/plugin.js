$(document).ready(function () {
    var redirectUri = $("meta[name='redirectUri']").attr('content');
    var clientId = $("meta[name='clientId']").attr('content');
    var urlParams = new URLSearchParams(window.location.search);
    var state = urlParams.get('return') || '';
    var html = '<div id="plugin_steamauth">\
        <a href="https://dev.arcengames.com/login_page.php?steam">Sign in with Steam</a>\
        </div>';
    $(html).insertAfter('#login-form');
});