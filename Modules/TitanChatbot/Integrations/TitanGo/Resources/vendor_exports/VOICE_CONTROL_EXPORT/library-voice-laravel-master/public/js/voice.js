window.onload = function(){
    if (annyang) {
        var commands = {
            /**Website Operations */          
            //return the page
            'go back': function(tags) {
                window.history.back();
            },
            //forward the page
            'go forward': function(tags) {
                window.history.forward();
            },
            //show the page 
            'show me *tags': function(tags) {
               if(tags=="book"||tags=="user"||tags=="loan"||tags=="fine"||tags=="home"||tags=="my details"){
                    if(document.getElementById("checkadmin").value == 0){
                        if(tags=="my details"){
                            window.location.href = "/user/show";
                        }
                        else{
                             window.location.href = "/user/"+tags;
                        }
                    }
                    else if(tags=="my details"){
                        window.location.href = "/user/show";
                    }
                    else{
                        window.location.href = "/"+tags;
                    } 
               }
               else{
                    var myWidth=100, myHeight=100;
                    var left = (screen.width - myWidth) / 2;
                    var top = (screen.height - myHeight) / 4;
                    var myWindow = window.open('Message','Message', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + myWidth + ', height=' + myHeight + ', top=' + top + ', left=' + left);
                    myWindow.document.write('Your command was not recognized. Please try again. ')
                    myWindow.focus()
                    setTimeout(function() {myWindow.close();}, 5000)
                    
               }
                
            },           
            //next table page
            'next page': function() {
                var urlParams = new URLSearchParams(window.location.search);
                var path=window.location.pathname;
                var page=Number(urlParams.get('page'))+1;
                window.location.href = path+"?page="+page;
            },
            //next page
            'page number *tags': function(tags) {
                    var path=window.location.pathname;
                    if(tags==='one'){
                        tags=1;
                    }
                    else if(tags==='to'){
                        tags=2;
                    }
                    else if(tags==='for'){
                        tags=4;
                    } 
                
                        var page=Number(tags);
                        window.location.href = path+"?page="+page;
        
            },


            /****************** Book Operations******************************/
            // title book form 
            'title *tags': function(tags) {
                document.getElementById('title').value = tags;
            },
            // author book form 
            'author *tags': function(tags) {
                document.getElementById('author').value = tags;
            },
            // Quantity book form 
            'quantity *tags': function(tags) {
                if(tags=='one'){
                    tags=1;
                }
                else if(tags=='to'){
                    tags=2;
                }
                else if(tags=='for'){
                    tags=4;
                }
                else if(tags=='five'){
                    tags=5;
                }  
                document.getElementById('quantity').value = tags;
            },

            // ISBN book form 
            'isbn *tags': function(tags) {
                document.getElementById('isbn').value = tags.replace(/ +/g, "");
            },
            // Publisher book form 
            'publisher *tags': function(tags) {
                document.getElementById('publisher').value = tags;
            },
            // Image book form 
            'image *tags': function(tags) {
                if(tags=="defualt"){
                    document.getElementById('image').value ="https://download-free-clip.art/wp-content/uploads/2018/05/picture-of-a-book-clipart-book-blue.jpg"
                }
                else{
                    document.getElementById('image').value = tags;
                }
                
            },
            // submit book form 
            'add book': function() {
                console.log('correct submit')
                document.getElementById('myform').submit();
            },
            // submit book form 
            'update book': function() {
                document.getElementById('myform').submit();
            },
            //search book
            'search book *tags': function(tags) {
                document.getElementById('search').value = tags;
            },
            //submit search book
            'search': function(tags) {
                document.getElementById('searchform').submit();
            },
            //Delete book
            'delete book *tags': function(tags) {
                if(tags==='one'){
                    tags=1;
                }
                else if(tags==='to'){
                    tags=2;
                }
                else if(tags==='for'){
                    tags=4;
                }
                else if(tags==='five'){
                    tags=5;
                }  
                document.getElementById(tags.toString()).submit();
            },           
            //edit book
            'EDIT BOOK *tags': function(tags) {
                console.log(tags);
                if(tags==='one'){
                    tags=1;
                }
                else if(tags==='to'){
                    tags=2;
                }
                else if(tags==='for'){
                    tags=4;
                }
                else if(tags==='five'){
                    tags=5;
                }  
            window.location.href = "/book/"+tags+"/edit";
            }, 
            //new book
            'new book': function() {
                window.location.href = "https://bookapp.test/book/create";
            },
            //category book
            'category *tags': function(tags) {
                var cat=tags.toLowerCase().replace(/ +/g, "");
                document.getElementById(cat).checked = true;
            },
             //uncheck category book
             'remove category *tags': function(tags) {
                var cat=tags.toLowerCase().replace(/ +/g, "");
                document.getElementById(cat).checked = false;
            },

            /*************************** Loans Operations*************************/
            // user email
            'user id *tags': function(tags) {
                if(tags==='one'){
                    tags=1;
                }
                else if(tags==='to'){
                    tags=2;
                }
                else if(tags==='for'){
                    tags=4;
                }
                else if(tags==='five'){
                    tags=5;
                }  
                document.getElementById('userid').value = tags;
            },
            //book isbn
            'book isbn *tags': function(tags) {
                document.getElementById('isbn').value = tags.replace(/ +/g, "");;
            },
            //new loan
            'new loan': function(tags) {
                window.location.href = "/loan/create";
            },
            //submit the search
            'submit': function(tags) {
                document.getElementById('myform').submit();
            },
            //submit the search
            'search': function(tags) {
                document.getElementById('searchform').submit();
            },
            //Return Loan
            'return loan *tags': function(tags) {
                if(tags==='one'){
                    tags=1;
                }
                else if(tags==='to'){
                    tags=2;
                }
                else if(tags==='for'){
                    tags=4;
                }
                else if(tags==='five'){
                    tags=5;
                }  
                document.getElementById(tags.toString()).submit();
            },   


            /** ********************User Operations **************************/
            'update': function() {
                document.getElementById('myform').submit();
            },
            //submit form
            'update user': function() {
                document.getElementById('myform').submit();
            },
            //edit user
            'edit user *tags': function(tags) {
                if(tags==='one'){
                    tags=1;
                }
                else if(tags==='to'){
                    tags=2;
                }
                else if(tags==='for'){
                    tags=4;
                } 
                if( Number(tags) <= document.getElementById('userNumber').value ){
                    window.location.href = "/user/"+tags+"/edit";
                        
                }
                else{
                    alert("User not found");
                }
            },
            //Delete user
            'delete user *tags': function(tags) {
                if(tags==='one'){
                    tags=1;
                }
                else if(tags==='to'){
                    tags=2;
                }
                else if(tags==='for'){
                    tags=4;
                }
                else if(tags==='five'){
                    tags=5;
                }  
                document.getElementById("user"+tags.toString()).submit();
            },
            //search user
            'search user *tags': function(tags) {
                document.getElementById('searchuser').value = tags;
            },
             //generete report
             'generate report': function() {
                window.location.href = "https://bookapp.test/dynamic_pdf/pdf"+window.location.pathname;
            },
            //First name
            'first name *tags': function(tags) {
                document.getElementById('first_name').value = tags;
            },
             //Last name
             'last name *tags': function(tags) {
                document.getElementById('last_name').value = tags;
            },
             //Email
             'email *tags': function(tags) {
                document.getElementById('email').value = tags;
            },
             //Phone number
             'phone number *tags': function(tags) {
                document.getElementById('phone_number').value = tags;
            },
             //Address
             'Address *tags': function(tags) {
                document.getElementById('address').value = tags;
            },
            //Admin
             'admin *tags': function(tags) {
                if(tags=="one"||tags=="zero"||tags=="1"||tags=="0"){
                    if(tags=='one'||tags=="1"){
                        document.getElementById('admin').value = 1;
                    }
                    else {
                        document.getElementById('admin').value = 0;
                    }
                 }
                 else{
                    alert("not valid number");
                 }
            },
            //Active
             'active *tags': function(tags) {
                 if(tags=="one"||tags=="zero"||tags=="1"||tags=="0"){
                    if(tags=='one'||tags=="1"){
                        document.getElementById('active').value = 1;
                    }
                    else {
                        document.getElementById('active').value = 0;
                    }
                 }
                 else{
                    alert("not valid number");
                 }
                
            },
             //image
             'image *tags': function(tags) {
                document.getElementById('image').value = tags;
            },

            /**Fines operation */
            //Pay Fine
            'pay fine number *tags': function(tags) {
                if(tags==='one'){
                    tags=1;
                }
                else if(tags==='to'){
                    tags=2;
                }
                else if(tags==='for'){
                    tags=4;
                }
                else if(tags==='five'){
                    tags=5;
                }  
                document.getElementById(tags.toString()).submit();
            }, 

        }
        annyang.addCommands(commands);
        annyang.start(); 
    }else {
       alert ("Voice Control is not available in this browser. Please use the Google Chrome.")
    }         
}