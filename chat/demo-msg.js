var msg = {
  uid: null,
  ajax: function(data, after) {
    let form = new FormData();
    for (let k in data) { form.append(k, data[k]); }
    fetch("3-ajax-msg.php", { method:"POST", body: form })
      .then(res => res.text())
      .then(txt => after(txt))
      .catch(err => console.error(err));
  },

  // Charger l’historique
  show: function(uid) {
    this.uid = uid;
    document.getElementById("mTxt").value = "";
    this.ajax({ req:"show", uid: uid }, txt => {
      document.getElementById("uMsg").innerHTML = txt;
      // scroller en bas
      let body = document.querySelector(".chat-body");
      body.scrollTop = body.scrollHeight;
    });
  },

  // Envoyer
  send: function() {
    let to = document.getElementById("toUid").value,
        text = document.getElementById("mTxt").value;
    this.ajax({ req:"send", to: to, msg: text }, res => {
      if (res==="OK") { this.show(to); }
      else          { alert(res); }
    });
    return false;
  }
};

// Au chargement, on init la discussion
window.addEventListener("DOMContentLoaded", () => {
  let uid = document.getElementById("toUid").value;
  msg.show(uid);
});
