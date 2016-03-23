var ChangeSetManager = {
	init: function() {
		$.each($('[data-deploymenu]'), function(){
			$(this).click(function(e){
				e.preventDefault();
				var menu = $(this).attr('data-deploymenu') == '' ? 0 : $(this).attr('data-deploymenu');
				$.ajax({
					url:'/fraym/deploy-change-set',
					dataType:'json',
					data:{menu:menu, undo: false},
					type:'post',
					success:function (data, textStatus, jqXHR) {
						window.location = window.location;
					}
				});
			});
		});

		$.each($('[data-undomenu]'), function(){
			$(this).click(function(e){
				e.preventDefault();
				var menu = $(this).attr('data-undomenu') == '' ? 0 : $(this).attr('data-undomenu');
				$.ajax({
					url:'/fraym/deploy-change-set',
					dataType:'json',
					data:{menu:menu, undo:true},
					type:'post',
					success:function (data, textStatus, jqXHR) {
						window.location = window.location;
					}
				});
			});
		});

		$.each($('[data-deployblock]'), function(){
			$(this).click(function(e){
				e.preventDefault();
				$.ajax({
					url:'/fraym/deploy-change-set',
					dataType:'json',
					data:{block:$(this).attr('data-deployblock'), undo:false},
					type:'post',
					success:function (data, textStatus, jqXHR) {
						window.location = window.location;
					}
				});
			});
		});

		$.each($('[data-undoblock]'), function(){
			$(this).click(function(e){
				e.preventDefault();
				$.ajax({
					url:'/fraym/deploy-change-set',
					dataType:'json',
					data:{block:$(this).attr('data-undoblock'), undo:true},
					type:'post',
					success:function (data, textStatus, jqXHR) {
						window.location = window.location;
					}
				});
			});
		});

		$('#deploy-all').click(function(e){
			e.preventDefault();
			$.ajax({
				url:'/fraym/deploy-change-set',
				dataType:'json',
				data:{undo:false},
				type:'post',
				success:function (data, textStatus, jqXHR) {
					window.location = window.location;
				}
			});
		});

		$('#undo-all').click(function(e){
			e.preventDefault();
			$.ajax({
				url:'/fraym/deploy-change-set',
				dataType:'json',
				data:{undo:true},
				type:'post',
				success:function (data, textStatus, jqXHR) {
					window.location = window.location;
				}
			});
		});
	}
};

$(function () {
	ChangeSetManager.init();
});