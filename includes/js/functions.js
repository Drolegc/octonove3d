function checkBeforeDelete(self) {

    if (window.confirm("Are you sure you want to delete?")) {
        console.log("deleting")
        self.parentNode.submit()
    }

}