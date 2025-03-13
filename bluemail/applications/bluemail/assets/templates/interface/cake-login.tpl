<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script>
    $(function(){
        $('#sub').click();
    });
</script>
<pre>Redirecting ....</pre>
<form action="$P{ACTION}/login.ashx?tp=1" method="post" style="visibility: hidden" autocomplete="off">
    <div id="document" class="group">
        <input type="hidden" name="u" value="$P{USERNAME}" id="u" placeholder="Username" />
        <input type="hidden" name="p" value="$P{PASSWORD}" id="password" placeholder="Password" />
        <input id="sub" type="submit" value="Log In"/>
    </div>
</form>