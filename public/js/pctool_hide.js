function $(id) {return document.getElementById(id)}
function $c(cl) {return document.getElementsByClassName(cl)}
function $n(name) {return document.getElementsByName(name)}
    function gray_on($class){
        for (var i=0; i< $c($class).length; i++){
            $c($class)[i].classList.add('gray');
            $c($class)[i].disabled = true;
            $c($class)[i].checked = false;
        }
    }
    function gray_off($class){
        for (var i=0; i< $c($class).length; i++){
            $c($class)[i].classList.remove('gray');
            $c($class)[i].disabled = false;
        }
    }

    function hide_on($class){
        for (var i=0; i< $c($class).length; i++){
            $c($class)[i].classList.add('hide');
            $c($class)[i].checked = false;
        }
    }
    function hide_off($class){
        for (var i=0; i< $c($class).length; i++){
            $c($class)[i].classList.remove('hide');
        }
    }

    //予約中の機材を表示するボタン
    window.addEventListener('DOMContentLoaded',function(){
        const show_used = document.getElementById('show_used'); // ①
        show_used.addEventListener('change',checksw,false);
        function checksw(){
            if( this.checked ){
                gray_on('chused');
                gray_on('trused');
            }else{
                gray_off('chused');
                gray_off('trused');

            }
        }
    });

    //準備中の機材を表示するボタン
    window.addEventListener('DOMContentLoaded',function(){
        const show_used = document.getElementById('show_prepare'); // ①
        show_used.addEventListener('change',checksw,false);
        function checksw(){
            if( this.checked ){
                hide_on('chprepared');
                hide_on('trprepared');
            }else{
                hide_off('chprepared');
                hide_off('trprepared');

            }
        }
    });
