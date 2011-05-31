/*
	За основу взят какой-то niceCheck и полностью переделан.
	Сделано на Фабрике сайтов
*/

function checkboxinit(){
	$(".niceCheck").each(
	function() {
		 changeCheckStart($(this));
	}).bind("click",function()
	{
		changeCheck($(this));
		sumka(); // Считаем сумму, т.к. заказана/отменена услуга
	});
};

/*
	функция смены вида и значения чекбокса
	el - span контейнер дял обычного чекбокса
	input - чекбокс
*/
function changeCheck(el)
{
	var el = el,
	input = el.find("input").eq(0);
	if(!input.attr("checked")) {
		el.css("background-position","0 0px");
		input.attr("checked", true)
	} else {
		el.css("background-position","0 -22px");
		input.attr("checked", false)
	}
	return true;
}


/*	если установлен атрибут checked, меняем вид чекбокса	*/
function changeCheckStart(el)
{
	var el = el,
	input = el.find("input").eq(0);
	if(input.attr("checked"))
	{
		el.css("background-position","0 0px");
	}
	return true;
}