// export function findLargestPalindrome(words) {

//   let palabras = [];

//   for (let i = 0; i < words.length; i++) {
//     let palindromo = '';

//     let palabra = words[i];

//     for (let j = 0; j < palabra.length; j++) {

//       palindromo = palabra[j] + palindromo;
//     }

//     if (palabra === palindromo) {
//       palabras.push({
//         palindromo: palindromo,
//         length: palindromo.length
//       });
//     }
//   }

//     if (palabras.length === 0) {
//       return null;
//     }

//     let masLarga = palabras[0];

//     for (let k = 0; k < palabras.length; k++) {
//       if (palabras[k].length > masLarga.length) {
//         masLarga = palabras[k];
//       }
//     }

//     console.log(masLarga.palindromo);
//     return masLarga.palindromo;
//   }


/*
//CLOUSER
export function createCalculator() {

  let duncan = '';

  return {
    add: function (num) {
      return duncan = +duncan + num;
    },

    subtract: function (num) {
      return duncan = duncan - num;
    },

    multiply: function (num) {
      return duncan = duncan * num;
    },

    divide: function (num) {
      return duncan = duncan / num;
    },

    clear: function () {
      return duncan = 0;
    },

    getTotal: function () {
      return duncan;
    }

  }

}
*/
export function createTaskPlanner(){
  let tasks = [];
  let id = 0;
  return {
    addTask: function(task){
      tasks.id = id++;
      tasks.name = task.name;
      tasks.priority = task.priority;
      tasks.tags = task.tags;
      task.completed = false;
      tasks.push(task);
    }
  }
}
console.log(task);