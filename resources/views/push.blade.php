<html>
<head>
    <script src="http://autobahn.s3.amazonaws.com/js/autobahn.min.js"></script>
    <script>
        var conn = new ab.Session('ws://<?php echo $_SERVER['SERVER_ADDR'];?>:9090',
                function() {
                    conn.subscribe('kittensCategory', function(topic, data) {
                        // This is where you would add the new article to the DOM (beyond the scope of this tutorial)
                        console.log('New article published to category "' + topic + '" : ' + data.title);
                    });
                },
                function() {
                    console.warn('WebSocket connection closed');
                },
                {'skipSubprotocolCheck': true}
        );
    </script>
</head>
<body>

</body>
</html>