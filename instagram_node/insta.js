var mongoose = require('mongoose');
mongoose.connect('mongodb://localhost/gteam');
Instagram = require('instagram-node-lib');
var _next_max_id = 0;
var items;
var Photo = mongoose.model('Photo', { 
	snid: String, 
	tags:Object,
	type:String,
	media:Object, 
	caption:Object,
	link: String, 
	date: String, 
	user: String, 
	likes:Object, 
	comments:Object 
});




Instagram.set('client_id', '833049b3d9a34818aeb9ca964b052107');
Instagram.set('client_secret', '705987c7316045c5ab54b85cc9fd39f9');
getPhotos();

function getPhotos(){
	if(_next_max_id == 0){
		console.log("aqui");
		Instagram.tags.recent({ 
			name: 'gcamp', 
			complete:onComplete, 
			error:onError 
		});		
	}else{

		Instagram.tags.recent({ 
			name: 'focusst', 
			max_tag_id:_next_max_id,
			complete:onComplete, 
			error:onError 
		});		
	}
	
}

function onComplete(data, pagination){
	console.log(pagination);

	if(pagination.next_max_id){
		_next_max_id  = pagination.next_max_id;
		console.log("aun hay más: " +_next_max_id);	
	 items = data;
	for (var item in items){

			Photo.find({ 'snid':  items[item].id}, function (err, docs) {
				console.log(docs.length);
		  		if(docs.length == 0){

					if(items[item].type != "video"){
						media = items[item].images.standard_resolution;
				 	}else{
			 			media =  items[item].videos.standard_resolution;
				 	}


					var p = new Photo({ 
						snid: items[item].id, 
						tags:items[item].tags,
						type:items[item].type,
						media:media, 
						caption:items[item].caption,
						link: items[item].link, 
						date: items[item].created_time, 
						user: items[item].user.username, 
						likes:items[item].likes, 
						comments:items[item].comments 
					});
					p.save();
		 	
		 		 }
			});						 		 	 
	}

	/*  console.log(item[0].id);	 
	 console.log(item[0].tags);	 
	 console.log(item[0].type);	
	 if(item[0].type != "video"){

	 		console.log(item[0].images.standard_resolution);

	 }else{
	 		console.log(item[0].videos.standard_resolution);
	 }
	console.log(item[0].caption);
	 console.log(item[0].link);
	 console.log(item[0].created_time);
	 console.log(item[0].user.username);
	 console.log(item[0].comments);
	 console.log(item[0].likes);*/


		getPhotos();


	}else{
		console.log("ya no hay");
	}




}
function onError(errorMessage, errorObject, caller){
	console.log(errorMessage);
}

