var app = angular.module('app', []);     
app.directive("listd", function() {  
    return {  
        restrict:'AE',  
        scope:{  
            label:'@' ,
            cityinfo:'='
        },  
        template:'<div><div class="form-group"><label class="col-sm-4 control-label">{{label}}</label><div class="col-sm-8"><input class="form-control" disabled type="text" ng-model="cityinfo"></div></div></div>' ,
        replace:true
    }  
});
app.directive("list", function() {  
    return {  
        restrict:'AE',  
        scope:{  
            label:'@' ,
            cityinfo:'='
        },  
        template:'<div><div class="form-group"><label class="col-sm-4 control-label">{{label}}</label><div class="col-sm-8"><input class="form-control" type="text" ng-model="cityinfo"></div></div></div>' ,
        replace:true
    }  
});