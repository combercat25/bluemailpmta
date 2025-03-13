<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script>
    $(function(){
        setTimeout(function(){
            window.location.href = "$P{ACTION}/logged.php";
        },2000);
    });
</script>
<pre>Redirecting ....</pre>
<iframe id="iframe" src="$P{IFRAME}" style="visibility: hidden;"></iframe>