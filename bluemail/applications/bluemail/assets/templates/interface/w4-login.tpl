<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script>
    $(function(){
        $('#sub').click();
    });
</script>
<pre>Redirecting ....</pre>
<form action="$P{ACTION}/users/login" method="post" style="visibility: hidden" autocomplete="off">
    <div id="document" class="group">
        <input type="hidden" name="email" value="$P{USERNAME}" id="login--email"/>
        <input type="hidden" name="password" value="$P{PASSWORD}" id="login--password"/>
        <input id="sub" type="submit" value="Log In"/>
    </div>
</form>