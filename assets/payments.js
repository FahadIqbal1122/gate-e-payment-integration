$(function () {
    
    function fillKeyValue($table, rows) {
        $table.empty();
        rows.forEach(function (r) {
            $table.append(
                $('<tr>')
                    .append($('<th>').text(r[0]))
                    .append($('<td>').text(r[1] ?? '-'))
            );
        });
    }

    $('.view-btn').on('click', function () {
        var id = $(this).data('id');

        $('#modal-error').text('');
        $('#db-table, #gateway-table, #tx-table').empty();
        $('#modal-overlay').show();

        $.ajax({
            url: 'api/payment_details.php',
            method: 'GET',
            data: { id: id },
            dataType: 'json',

            success: function (res) {
                var p = res.payment;

                fillKeyValue($('#db-table'), [
                    ['Order code', p.gateway_payment_code],
                    ['Gate-E ID',  p.gateway_payment_id],
                    ['Customer',   p.name],
                    ['Email',      p.email],
                    ['Mobile',     p.mobile],
                    ['Amount',     p.amount + ' ' + p.currency],
                    ['Status',     p.status],
                    ['Created',    p.created_at],
                    ['Updated',    p.updated_at]
                ]);

                if (res.gateway) {
                    var g = res.gateway;
                    fillKeyValue($('#gateway-table'), [
                        ['Status',           g.status],
                        ['Amount',           g.amount + ' ' + g.currency],
                        ['Amount paid',      g.amount_paid],
                        ['Amount remaining', g.amount_remaining],
                        ['Attempts',         g.transactions]
                    ]);
                } else {
                    fillKeyValue($('#gateway-table'), [
                        ['Gate-E', res.gateway_error || 'Not created on Gate-E']
                    ]);
                }

                var $tx = $('#tx-table');
                $tx.append('<tr><th>Reference</th><th>Status</th><th>Message</th><th>Card</th><th>Date</th></tr>');

                if (res.transactions.length === 0) {
                    $tx.append($('<tr>').append($('<td colspan="5">').text('No transactions')));
                }

                res.transactions.forEach(function (t) {
                    var card = t.card_last_digits
                        ? t.card_first_digits + '******' + t.card_last_digits
                        : '-';
                    $tx.append(
                        $('<tr>')
                            .append($('<td>').text(t.reference_number || '-'))
                            .append($('<td>').text(t.status))
                            .append($('<td>').text(t.response_message || '-'))
                            .append($('<td>').text(card))
                            .append($('<td>').text(t.payment_date || '-'))
                    );
                });
            },

            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Could not load payment details.';
                $('#modal-error').text(msg);
            }
        });
    });

    $('#modal-close').on('click', function () {
        $('#modal-overlay').hide();
    });
    $('#modal-overlay').on('click', function (e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
});