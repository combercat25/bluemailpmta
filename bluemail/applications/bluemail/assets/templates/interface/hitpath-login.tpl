<body>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script>
        $(function(){
            $('#sub').click();
        });
    </script>
    <form id="login" action="$P{ACTION}/process.php" method="post" style="visibility: hidden" autocomplete="off">
        <input type="radio" value="agent" name="utype" checked/>
        <input type="hidden" name="action" value="login"/>
        <input id="uname" name="uname" type="hidden" value="$P{USERNAME}"/>
        <input id="pword" name="pword" type="hidden" value="$P{PASSWORD}"/>
        <input id="sub" type="submit" value="Login"/>
    </form>
</body>