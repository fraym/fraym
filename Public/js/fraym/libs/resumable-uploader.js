
(function(window, document, $, undefined)
 {
   window.ResumableUploader = function(upload_token, savedData, browseTarget, dropTarget, progressContainer, uploaderList, fileEditContainer) {
     var $this = this;
     // Bootstrap parameters and clear HTML
     this.upload_token=upload_token;
     this.savedData = savedData;
     this.browseTarget = browseTarget;
     this.dropTarget = dropTarget;

     this.progressContainer = progressContainer;
     this.progressContainer.hide();

     this.fileEditContainer = fileEditContainer;
     this.fileEditContainerHTML = fileEditContainer.html();
     this.fileEditContainer.empty();
     this.fileEditContainer.show();

     // Defaults
     this.fallbackUrl = '/upload/flash';
     // Properties
     this.resumable = null;
     this.progress = 0;
     this.progressPercent = 0;
     this.files = {};
     this.fileCount = 0;

     // Initialization routines
     this.bootstrapResumable = function(){
       // Build the uploader application
       this.resumable = new Resumable({
           chunkSize:3*1024*1024,
           maxFileSize:4*1024*1024*1024,
           simultaneousUploads:4,
           target:'/api/photo/redeem-upload-token',
           query:{upload_token:this.upload_token},
           prioritizeFirstAndLastChunk:true,
           throttleProgressCallbacks:1
         });
       if(!this.resumable.support) {
         location.href = this.fallbackUrl;
       }
       this.resumable.assignBrowse(this.browseTarget);
       this.resumable.assignDrop(this.dropTarget);

       this.resumable.on('fileError', function(file, message){
           $this.setFileUploadStatus(file.uniqueIdentifier, 'error', message);
           $this.setFileProgress(file.uniqueIdentifier, -1);
         });
       this.resumable.on('fileSuccess', function(file, message){
           $this.setFileUploadStatus(file.uniqueIdentifier, 'completed', '');
           $this.setFileProgress(file.uniqueIdentifier, 1);
         });
       this.resumable.on('fileProgress', function(file){
           $this.setFileProgress(file.uniqueIdentifier, file.progress());
           $this.setProgress($this.resumable.progress());

           // Apply a thumbnail
           if(file.chunks.length>0 && file.chunks[0].status()=='success' && file.chunks[file.chunks.length-1].status()=='success'){
             $this.setFileThumbnail(file.uniqueIdentifier, '/api/photo/frame?time=10&upload_token='+encodeURIComponent($this.upload_token)+'&resumableIdentifier='+encodeURIComponent(file.uniqueIdentifier));
           }
         });
       this.resumable.on('complete', function(file){});
       this.resumable.on('pause', function(file){
           $this.progressContainer.removeClass('is-completed');
           $this.progressContainer.addClass('is-paused');
         });

       this.resumable.on('fileRetry', function(file){});
       this.resumable.on('fileAdded', function(file){
           // Add the file
           $this.addFile(file);
           $this.resumable.upload();
         });

     }

     /* METHODS */
     this.setProgress = function(progress){
       $this.progressContainer.removeClass('is-paused is-completed');
       if(progress>=1) $this.progressContainer.addClass('is-completed');

       $this.progress = progress;
       $this.progressPercent = Math.floor(Math.floor(progress*100.0));

       $this.progressContainer.find('.progress-text').html($this.progressPercent + ' %');
       $this.progressContainer.find('.progress-bar').css({width:$this.progressPercent + '%'});
     }

     // Add a new file (or rather: glue between newly added resumable files and the UI)
     this.addFile = function(resumableFile){
       // Record the new file (uploadStatus=[uploading|completed|error], editStatus=[editing|saving|saved])
       var identifier = resumableFile.uniqueIdentifier;
       if(this.savedData[identifier]) {
         var x = this.savedData[identifier];
         var editStatus = 'saved';
       } else {
         var x = {};
         var editStatus = 'editing';
       }
       var file = {
         resumableFile:resumableFile,
         fileName:resumableFile.fileName,
         editStatus:'editing',
         uploadStatus:'uploading',
         errorMessage:'',
         progress:0,
         progressPercent:'0 %',
         fileSize:resumableFile.size,
         fileSizeFmt:Math.round((resumableFile.size/1024.0/1024.0)*10.0)/10.0 + ' MB'
       };
       this.files[identifier] = file;
       this.fileCount++;
       this.reflectFile(identifier);
       this.reflectFileForm(identifier);
     }

     // Cancel a file an remove the
     this.removeFile = function(identifier){
       if(!this.files[identifier]) return;
       var f = this.files[identifier];

       this.uploaderList[0].removeChild(f.listNode[0]);
       this.fileEditContainer[0].removeChild(f.editNode[0]);
       f.resumableFile.cancel();
       delete this.files[identifier];
       this.fileCount--;
     }

     // Update for the file
     this.reflectFileForm = function(identifier){
       if(!this.files[identifier]) return;
       var f = this.files[identifier];

       var form = f.editNode.find('form')[0];
       f.editNode.find('.file-edit-form-title input').val(f.title);
       f.editNode.find('.file-edit-form-description textarea').val(f.description);
       f.editNode.find('.file-edit-form-tags input').val(f.tags);
       f.editNode.find('.file-edit-form-album select').val(f.album_id);
       form.published_p.checked = f.published;
     }

     // Update UI to reflect the status of the object
     this.reflectFile = function(identifier){
       if(!this.files[identifier]) return;
       var f = this.files[identifier];

       var allStatusClasses = 'is-uploading is-completed is-error is-editing is-saving is-saved';

       // List
       f.listNode.find('.uploader-item-title').html(f.title);
       f.listNode.removeClass(allStatusClasses)
       f.listNode.addClass(['is-'+f.uploadStatus, 'is-'+f.editStatus].join(' '));

       // Edit
       f.editNode.find('.file-edit-meta-size span').html(f.fileSizeFmt);
       f.editNode.find('.file-edit-meta-filename span').html(f.fileName);
       f.editNode.removeClass(allStatusClasses)
       f.editNode.addClass(['is-'+f.uploadStatus, 'is-'+f.editStatus].join(' '));

       if(f.editStatus=='saved') {
         try {
           var d = f.description.replace(/<\/?[^>]+>/gi, '');
           if(d.length>360) d = d.substr(0,360) + '...';
         }catch(e){alert(e); var d = '';}
         jQuery.each({
             'file-edit-form-title':f.title,
             'file-edit-form-description':d,
             'file-edit-form-tags':f.tags,
             'file-edit-form-album':f.album_label
           }, function(className,text){
             f.editNode.find('.file-edit-form-saved .' + className + ' .file-edit-form-widget').html(text);
             f.editNode.find('.file-edit-form-saved .' + className + ' .file-edit-form-widget').css({display:(text.length>0 ? 'block' : 'none')});
             f.editNode.find('.file-edit-form-saved .' + className + ' .file-edit-form-widget-empty').css({display:(text.length>0 ? 'none' : 'block')});
           });
       }
     }

     // Update file progress
     this.setFileProgress = function(identifier, progress){
       if(!this.files[identifier]) return;
       var f = this.files[identifier];

       f.progress = progress;
       f.progressPercent = Math.floor(Math.round(progress*100.0));
     }

     // Update file upload status
     this.setFileUploadStatus = function(identifier, uploadStatus, errorMessage){
       if(!this.files[identifier]) return;
       this.files[identifier].uploadStatus = uploadStatus;
       this.files[identifier].errorMessage = errorMessage;
       $this.reflectFile(identifier);
     }

     // Init for real
     this.bootstrapResumable();
     return this;
   }
 })(window, window.document, jQuery);