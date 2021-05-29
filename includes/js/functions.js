function checkBeforeDelete(self) {

    if (window.confirm("Are you sure you want to delete?")) {
        self.parentNode.submit()
    }

}