!function(n){formintorjs.define(["admin/models/base-model","admin/models/answers-collection"],function(n,e){return n.extend({defaults:{answers:!1},initialize:function(){var n=arguments;!1===this.get("answers")&&this.set("answers",new e),n&&n[0]&&n[0].answers&&(n.answers=n[0].answers instanceof e?n[0].answers:new e(n[0].answers),this.set("answers",n.answers))},get_id:function(){return this.cid},find_answers_with_no_result:function(){var n=this.get("answers"),e=[];return _.isUndefined(n)?e:this.answers_count()<1?e:(e=n.filter(function(n){return!n.get("result")}),_.isUndefined(e)||e.length<1?[]:e)},answers_count:function(){return this.get("answers").length}})})}(jQuery);