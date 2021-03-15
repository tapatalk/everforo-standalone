<html>
    <head>
    </head>
    <style>
        .footer-text-a:link {
            color: #696969;
            } 
        .footer-text-a:active{
            color: #696969; 
        }
        .footer-text-a:visited {
            color:#696969;
            }
        .footer-text-a:hover {
            color: #696969;
            }
        .container-a:link {
            color: #2C88F3;
            } 
        .container-a:active{
            color: #2C88F3; 
        }
        .container-a:visited {
            color:#2C88F3;
            }
        .container-a:hover {
            color: #2C88F3;
            }

    </style>
    <body style="
        padding: 0;
        margin: 0;
        background-color: #ECEFF1;
        color:black;
        width:600px;
        margin-left:auto;
        margin-right:auto;
    ">
        <div class="head" style="width: 100%;
            height: 90px;
            background-color: #3d72de;
            ">
                <img style="
                    width: 50px;
                    height: 50px;
                    margin-left: 20px;
                    margin-top:20px;
                    float:left;
                    "
                class="icon" src="{{$domain}}/img/logo@2x.png">
                <span style="color: white;
                    margin-left: 10px;
                    font-size: 25px;
                    font-weight: 700;
                    margin-top:25px;
                    float:left;
                "
                class="text">{{$group_title}}</span>
        </div>
        <div class="container" style="height: 500px;
            padding-left: 5%;
            padding-top: 5%;
            padding-right: 5%;
            font-size: 16px;
            background-color: white;
            color:black;
            ">
            <!-- <strong>{{$user_name}}</strong> {{$type}} to {{$to_user}} post in <strong><</strong><strong>{{$thread_title}}</strong><strong>></strong> -->
            Hi,
            <br/><br/>
            <span style="font:20px">We just want to let you know <strong>{{$user_name}}</strong> {{$type}} {{$to_user}} post in <strong>{{$thread_title}}</strong>. </span>
            <br/>
            <br/><br/>
            To view the interaction, please click the following link:
            <a class="container-a" style="color: #2C88F3;" href="{{$post_url}}">{{$post_url}}</a>
            <br/>
            <br/><br/>

            To unsubscribe from all future updates, click the following link:
            <br/>
            <a class="container-a" style="color: #2C88F3;" href="{{$unsubscribe_url}}">{{$unsubscribe_url}}</a>
            <br/>
            <br/><br/>

            Regards,<br/>
            {{$group_title}}
            <br/>
            <br/><br/>
        </div>
        <div class="footer" style="width: 100%;
            background-color: #ECEFF1;
            padding-top: 10px;
            float:left;
            ">
            <div class="footer-text" style="text-align: center;
                    margin-bottom: 15px;
                    ">
                    <a class="footer-text-a" style="text-decoration : none; color: #2C88F3;" href="{{$domain}}"><span>
                        Privacy Policy
                    </span></a>
                    |
                    <a class="footer-text-a" style="text-decoration : none; color: #2C88F3;" href="{{$domain}}"><span>
                        @2021 {{$group_title}}, Inc.
                    </span></a>
                </div>
        </div>
    </body>
</html>