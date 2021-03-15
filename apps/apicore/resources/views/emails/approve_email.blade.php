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
        <div class="container" style="height: 550px;
            padding-left: 5%;
            padding-top: 5%;
            padding-right: 5%;
            font-size: 16px;
            background-color: white;
            color:black;
            text-align:center;
            ">
            <img 
                style="
                    width: 50px;
                    height: 50px;
                    border-radius:25px;
                "
                class="icon"
                src="{{$group_cover}}"
            >
            <div style="font-size:20px">
                {{$msg}}
            </div>
            <br/>
            <div style="
                margin-top:25px;
                margin-bottom:25px;
                padding-top:25px;
                padding-bottom:25px;
                border-top: 1px solid #f2f2f2;
            ">
            We just want to let you know that your request to join {{$group_title}} is now approved. To start participating in this group,click here:
                <br />
                <a href="{{$group_url}}">
                    {{$group_url}}
                </a>
            </div>
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