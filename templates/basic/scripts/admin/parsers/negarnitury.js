var count = 0;
var current_str = 2; // Первую-вторую строку не учитываем
var err = 0;
var success = 2; // Первая-вторая строка всегда ок
var double = 0;

$("#get").click(function(){
   $.ajax({
      type: 'post',
      url: '/admin/parser/negarnitury/count',
      data: 1,
      dataType: 'json',
      success: function(c){
         count = c.count;
         $("#count").html(count);
      }
   });
});

$("#upload").click(function(){
   var interval = setInterval(function(){
      if(current_str == count - 1 || count == 0) clearInterval(interval);
      if(count == 0) return false;

      $.ajax({
         type: 'post',
         url: '/admin/parser/negarnitury/parse',
         data: {
            str: current_str
         },
         dataType: 'json',
         success: function(c){
            if(c.err == 0) $("#success").html(++success);
            else $("#err").html(++err);

            if(c.double > 0) $("#double").html(++double);
         }
      });

      current_str++;
   }, 100);
});
