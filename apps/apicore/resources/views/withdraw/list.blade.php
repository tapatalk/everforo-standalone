
<html>
    <head>
        <title>welcome</title>
    </head>
    <body>
        <div class="container" style="margin-top: 20px;margin-left: 20px;">

        <table>
            <thead>
                <tr>
                    <th >order id/address/payment id/transactionHash</th>
                    <th >user </th>
                    <th >token </th>
                    <th >status </th>
                    <th >platform </th>
                    <th >created_at </th>
                    <th >options </th>
                </tr>
            </thead>

            <tbody id="data-container">

            </tbody>


        </table>
            <div id="pagination"></div>

        {{--Url: <a href="{{ $_url }}">{{ $_url }}</a>--}}

        <script src="https://code.jquery.com/jquery-1.8.2.min.js"></script>
        <script src="{{ url('',[],true) }}/pagination.js"></script>
        </div>
    </body>

<style>
    td{
        padding: 10px;
    }

    ul{
        float: right;
    }
    ul li {
        margin: 2px;
        list-style-type: none;
    }
    ul li {
        display: inline-block;
    }

</style>

<script>
    $(function() {
        (function(name) {
            var container = $('#' + name);
            container.pagination({
                dataSource: '{{ url('withdraw/list',[],true)  }}',
                locator: 'data.list',
                totalNumber: {{ $totalnumber }},
                pageSize: {{ $pagesize }},
                ajax: {
                    beforeSend: function() {
                        $('#data-container').html('Loading data ...');
                    }
                },
                callback: function(response, pagination) {
                    var dataHtml = '';

                    $.each(response, function (index, item) {
                        dataHtml += ' <tr>';
                        dataHtml += '<td>';
                        dataHtml +=  '<div>'  +  item.order_id  + '</div>';
                        dataHtml +=  '<div>'  +  item.to  + '</div>';
                        dataHtml +=  '<div>'  +  item.payment_id  + '</div>';
                        dataHtml +=  '<div>'  +  item.transactionHash  + '</div>';

                        dataHtml +=    '</td>';
                        dataHtml += '<td>' + item.user_id + '</td>';
                        dataHtml += '<td>' + item.token_id + '</td>';
                        if(item.is_paid == 1){
                            if(item.status == 3){
                                dataHtml += '<td>' + 'transferring' + '</td>';
                            } else if(item.status == 4){
                                dataHtml += '<td>' + 'transferred' + '</td>';
                            } else {
                                dataHtml += '<td>' + 'paid' + '</td>';
                            }
                        } else if(item.deleted_at != null){
                            dataHtml += '<td>' + 'cancel' + '</td>';
                        } else {
                            dataHtml += '<td> status code: ' + item.status + '</td>';
                        }



                        dataHtml += '<td>' + item.platform + '</td>';

                        dataHtml += '<td>' + item.created_at + '</td>';
                        dataHtml += '<td>';
                        if(item.is_paid == 1 && item.status == 2){
                            dataHtml += '<button id='+item.id+' onclick="transafer('+item.id+')">transafer</button>';
                        } else {
                            // dataHtml += 'empty';
                        }
                        dataHtml += '</td>';

                        dataHtml += '</tr>';
                    });

                    $('#data-container').html(dataHtml);
                }
            })
        })('pagination');
    })

    function transafer(id){

        $('#'+id).attr("disabled","true");
        $('#'+id).attr("disabled",true);
        $('#'+id).attr("disabled","disabled");

        var href =  '{{ url('withdraw/transafer',[],true) }}';

        var data = new FormData();
        data.append('id',id);

        $.ajax({
            url        : href,
            type       : 'POST',
            dataType   : 'json',
            processData: false,
            contentType: false,
            data       : data,
            error      : function (response) {
                alert('error');
                console.log(response);
            },
            success    : function (response) {
                if(response.data.transafer == 1){
                    $('#'+id).html('transferring');
                    alert('transferring');

                } else {
                    alert('error');
                }

            }
        });
    }

</script>

</html>