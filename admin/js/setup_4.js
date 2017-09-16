function addForumCats(forumCats) {
    window.forumCats = forumCats;
    var i;
    for (i = 0; i < forumCats.length; i++) {
        window.cats[i] = forumCats[i];
    }
    refreshTable();
}

var cats = [];

var table = document.getElementById('category_table').childNodes[1];

var catForm = {
    state: 'add',
    form: document.getElementById('cat_form') || document.getElementById('cp_form'),
    
    init: function() {
        this.nameField = this.form.name,
        this.descriptionField = this.form.description,
        this.addBtn = this.form.addBtn;
    },
    
    header: document.getElementById('add_cat_h4'),
    
    getName: function() {
        return this.nameField.value.trim();
    },
    getDescription: function() {
        return this.descriptionField.value.trim();
    },
    
    setName: function(name) {
        this.nameField.value = name;
    },
    setDescription: function(description) {
        this.descriptionField.value = description;
    },
    setState: function(state, cat) {
        if (state == 'edit' || state == 'editLock') {
            this.state = state;
            
            this.header.innerHTML = 'Edit Category';
            this.addBtn.value = 'Edit';
            
            if (cat != null) {
                this.setName(cat.name);
                this.setDescription(cat.description);
            }
            if (this.state == 'editLock') {
                this.editCatName = cat.name;
            }
        } else {
            this.state = 'add';
            this.header.innerHTML = 'Add Category';
            this.addBtn.value = 'Add';
            this.editCatName = null;
        }
    },
    setFormError: function(err) {
        document.getElementById('error').innerHTML = err;
    },
    setFieldError: function(field, err) {
        var errSpan = document.getElementsByClassName('error_' + field)[0];
        errSpan.innerHTML = err;
    },
    clearErrors: function() {
        document.getElementById('error').innerHTML = '';
        document.getElementsByClassName('error_name')[0].innerHTML = '';
        document.getElementsByClassName('error_description')[0].innerHTML = '';
    },
    addHiddenField: function(name, value) {
        this.form.innerHTML += "<input type='hidden' name='"+name+"' value='"+value+"' />";
    },
    reset: function() {
        this.state = 'add';
        this.header.innerHTML = 'Add Category';
        this.setName('');
        this.setDescription('');
        this.addBtn.value = 'Add';
    }
};
catForm.init();
catForm.nameField.onkeyup = function() {
    if (catForm.state == 'editLock') {
        return;
    }
    
    if (getCatIndex(cats, this.value) != -1) {
        catForm.setState('edit', null);
    } else {
        catForm.setState('add', null);
    }
}

function refreshTable() {
    table.innerHTML = '';
    var i;
    for (i = 0; i < cats.length; i++) {
        table.innerHTML += '<div class="row" title="Click to edit">'
                + '<div class="col col1">' + cats[i].name + '</div>'
                + '<div class="col col2">' + cats[i].description + '</div>'
                + '<a href="#" class="remove">Remove</a>'
                + '<div class="clearfix"></div>'
                + '</div>';
    }
    table.scrollTop = table.scrollHeight;
     
    var rows = table.getElementsByClassName('row');
    for (i = 0; i < rows.length; i++) {
        rows[i].data = cats[i];
        rows[i].onclick = function() {
            catForm.setState('editLock', this.data);
        }
        rows[i].getElementsByClassName('remove')[0].onclick = function(e) {
            cats.splice(cats.indexOf(this.parentNode.data), 1);
            refreshTable();
            e.stopPropagation();
            e.preventDefault();
        }
    }
}

function addCat() {
    var nameOk = true, descriptionOk = true;
    
    catForm.clearErrors();
    
    if (catForm.getName() == '') {
        catForm.setFieldError('name', 'Field is required');
        nameOk = false;
    }
    if (catForm.getDescription() == '') {
        catForm.setFieldError('description', 'Field is required');
        descriptionOk = false;
    }
    
    if (!nameOk || !descriptionOk) {
        return;
    }
    
    var i;
    if (catForm.state == 'editLock') {
        i = getCatIndex(cats, catForm.editCatName);
    } else {
        i = getCatIndex(cats, catForm.getName());
    }
    
    if (i != -1) {
        cats[i].modify(catForm.getName(), catForm.getDescription());
    } else {
        if (window.forumCats && (i = getCatIndex(forumCats, catForm.getName())) != -1) {
            cats.push(forumCats[i].modify(catForm.getName(), catForm.getDescription()));
        } else {
            cats.push(new Category(null, catForm.getName(), catForm.getDescription()));
        }
    }
    catForm.reset();
    refreshTable();
}

function getCatIndex(catArray, name) {
    var i;
    for (i = 0; i < catArray.length; i++) {
        if (catArray[i].name.toLowerCase() == name.toLowerCase()) {
            return i;
        }
    }
    return -1;
}

function addToForm(e) {
    if (cats.length == 0) {
        catForm.setFormError('Please add a category.');
        e.preventDefault();
    } else {
        catForm.addHiddenField('cats', JSON.stringify(cats));
    }
}

function Category(id, name, description) {
    if (id != null) {
        this.id = id;
    }
    this.name = name.trim(),
    this.description = description.trim();
    
    this.modify = function(name, description) {
        this.name = name.trim(),
        this.description = description.trim();
        return this;
    };
}
