/**
 * Emerge Mono - Cookie consent banner.
 * Shows the banner until the visitor accepts or declines. Stores the choice in a
 * first-party cookie for 182 days. No external service is contacted.
 */
(function(){
    function getCookie(name) {
        var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
        return v ? v[2] : null;
    }
    function setCookie(name, value, days) {
        var d = new Date();
        d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = name + '=' + value + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
    }

    var banner = document.getElementById('en-cookie-banner');
    if ( ! banner ) { return; }

    var consent = getCookie('en_cookie_consent');
    if ( ! consent ) {
        banner.style.display = 'block';
    }

    window.enCookieAccept = function() {
        setCookie('en_cookie_consent', 'accepted', 182);
        banner.style.display = 'none';
    };
    window.enCookieDecline = function() {
        setCookie('en_cookie_consent', 'declined', 182);
        banner.style.display = 'none';
    };
})();
