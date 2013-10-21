

<div id="slider" style="position:absolute; top:10px; left:50%;">
	Lulz, erster Text.<br />
	noch mehr.

<!-- <div id="slider-id" style="width: 492px; margin: 10px;"><div class="ui-slider-handle"></div> -->


<div id="slider"></div> <!-- the Slider -->

<script>

		$(function() {

			//Store frequently elements in variables
			var slider  = $('#slider'),
				tooltip = $('.tooltip');

			//Hide the Tooltip at first
			tooltip.hide();

			//Call the Slider
			slider.slider({
				//Config
				range: "min",
				min: 0,
				value: 0.5,
				step: 0.01,
				max: 1,

				start: function(event,ui) {
				    layer_mapnik.setVisibility(true);
				    layer_bing_aerial.setOpacity(0.5);
				},

				//Slider Event
				slide: function(event, ui) { //When the slider is sliding

					var value  = slider.slider('value'),
						volume = $('.volume');

					tooltip.css('left', value).text(ui.value);  //Adjust the tooltip accordingly

					if(value <= 5) { 
						volume.css('background-position', '0 0');
					} 
					else if (value <= 25) {
						volume.css('background-position', '0 -25px');
					} 
					else if (value <= 75) {
						volume.css('background-position', '0 -50px');
					} 
					else {
						volume.css('background-position', '0 -75px');
					};
					console.log(value);
					layer_bing_aerial.setOpacity(value);


				},

				stop: function(event,ui) {
				    tooltip.fadeOut('fast');
				},
			});

		});
	</script>

</div>