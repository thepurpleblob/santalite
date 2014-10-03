$(".santa-tooltip").tooltip();

$(".reconcile").confirm({
	text: "Has this transaction been paid in SagePay?",
	title: "Reconcile with SagePay",
	confirm: function(button) {
		$.post( button.val() + '/OK' );
		button.attr('class', 'btn btn-default btn-sm');
		button.attr('disabled', true);
		button.text('OK')
	},
	cancel: function(button) {
		$.post( button.val() + '/UNPAID' );
		button.attr('class', 'btn btn-default btn-sm');
		button.attr('disabled', true);
		button.text('UNPAID')
	},
	confirmButton: "Yes",
	cancelButton: "No"
});

$(".auto-submit").change(function() {
	this.submit();
})