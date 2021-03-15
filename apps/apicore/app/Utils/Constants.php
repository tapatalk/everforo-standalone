<?php

namespace App\Utils;

class Constants{


    const USERNAME_MIN_LENGTH = 3;
    const USERNAME_MAX_LENGTH = 32;
    const PASSWORD_MIN_LENGTH = 8;
    const PASSWORD_MAX_LENGTH = 32;
    const DEVICE_ID_LENGTH    = 32;

    const reserved_names = array("admin","blog","dev","ftp","mail","pop","pop3","imap","smtp","stage","stats","status","www","beta","about","access","account","accounts","add","address","adm","admin","administration","adult","advertising","affiliate","affiliates","ajax","analytics","android","anon","anonymous","api","app","apps","archive","atom","auth","authentication","avatar","backup","banner","banners","bin","billing","blog","blogs","board","bot","bots","business","chat","cache","cadastro","calendar","campaign","careers","cgi","client","cliente","code","comercial","compare","config","connect","contact","contest","create","code","compras","css","dashboard","data","db","design","delete","demo","design","designer","dev","devel","dir","directory","doc","docs","domain","download","downloads","edit","editor","email","ecommerce","forum","forums","faq","favorite","feed","feedback","flog","follow","file","files","free","ftp","gadget","gadgets","games","guest","group","groups","help","home","homepage","host","hosting","hostname","html","http","httpd","https","hpg","info","information","image","img","images","imap","index","invite","intranet","indice","ipad","iphone","irc","java","javascript","job","jobs","js","knowledgebase","log","login","logs","logout","list","lists","mail","mail1","mail2","mail3","mail4","mail5","mailer","mailing","mx","manager","marketing","master","me","media","message","microblog","microblogs","mine","mp3","msg","msn","mysql","messenger","mob","mobile","movie","movies","music","musicas","my","name","named","net","network","new","news","newsletter","nick","nickname","notes","noticias","ns","ns1","ns2","ns3","ns4","ns5","ns6","ns7","ns8","ns9","ns10","old","online","operator","order","orders","page","pager","pages","panel","password","perl","pic","pics","photo","photos","photoalbum","php","plugin","plugins","pop","pop3","post","postmaster","postfix","posts","profile","project","projects","promo","pub","public","python","random","register","registration","root","ruby","rss","sale","sales","sample","samples","script","scripts","secure","send","service","shop","sql","signup","signin","search","security","settings","setting","setup","site","sites","sitemap","smtp","soporte","ssh","stage","staging","start","subscribe","subdomain","suporte","support","stat","static","stats","status","store","stores","system","tablet","tablets","tech","telnet","test","test1","test2","test3","teste","tests","theme","themes","tmp","todo","task","tasks","tools","tv","talk","update","upload","url","user","username","usuario","usage","vendas","video","videos","visitor","win","ww","www","www1","www2","www3","www4","www5","www6","www7","wwww","wws","wwws","web","webmail","website","websites","webmaster","workshop","xxx","xpg","you","yourname","yourusername","yoursite","yourdomain");
    
    const LOADMORE_THRESHOLD = 5;

    const PRODUCT_ID_CREATE_TOKEN = 1;
    const PRODUCT_ID_WITHDRAW_TOKEN = 2;


    const CHANNEL_ID_AIRDROP = 1;
    const CHANNEL_ID_ORDER = 2;
    const CHANNEL_ID_WITHDRAW = 3;

    const ORDER_CREATED = 1;
    const ORDER_PAID = 2;

    const FEATURE_STATUS = 1;
    const SUBSCRIBE_FEATURE = 2;
    const SHARE_FEATURE = 3;
    const ATTACHMENTS_FEATURE = 4;
    const MODERATOR_FEATURE = 5;
    const GROUP_PRIVACY_FEATURE = 6;

    const GROUP_ADMIN_PIN = 0;
    const SUPER_ADMIN_PIN = 1;

    public static function get_etherscan_link(){
        if(env('APP_ENV') === 'local' || env('APP_ENV') === 'dev'|| env('APP_ENV') === 'stage'){
            return
                'https://rinkeby.etherscan.io/';
        } else {
            return
                'https://etherscan.io/';
        }
    }

}



