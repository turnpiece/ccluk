!function(n){formintorjs.define(["admin/models/base-model","admin/models/conditions-collection"],function(n,i){return n.extend({defaults:{condition_action:"show",condition_rule:"any",conditions:!1},initialize:function(){var n=arguments;!1===this.get("conditions")&&this.set("conditions",new i),n[0]&&n[0].conditions&&(n.conditions=n[0].conditions instanceof i?n[0].conditions:new i(n[0].conditions),this.set("conditions",n.conditions))},add_to:function(n,i,t){t=_.isObject(t)?t:{};var o=this,e=[],s=!1;n.each(function(n,t){t==i&&(e.push(o),s=!0),e.push(n)}),s?(n.reset(e,{silent:!0}),n.trigger("add",this,n,_.extend(t,{index:i}))):n.add(this,_.extend(t,{index:i}))},get_id:function(){return this.cid},get_conditions_element_ids:function(){var n=[];if(!_.isUndefined(this.get("conditions"))&&this.get("conditions").length>0){this.get("conditions").each(function(i){n.push(i.get("element_id"))})}return n},clone_deep:function(){var n=new this.constructor(this.attributes);return _.each(this.attributes,function(i,t){if(Array.isArray(i)){var o=[];_.each(i,function(n){o.push(_.clone(n))}),n.set(t,o)}}),n.set("conditions",new i(n.get("conditions").toJSON())),n}})})}(jQuery);