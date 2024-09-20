import { Component } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { AbstractControl, FormBuilder, FormGroup, Validators} from '@angular/forms';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  peliculas: any | null = null;
  generos: any | null = null;
  miFormulario: FormGroup;

  constructor (private http: HttpClient, private formBuilder: FormBuilder) {
    this.miFormulario = this.formBuilder.group({
      titulo  : ['', [Validators.required]],
      anio    : ['', [Validators.required]],
      generos : [[], []],
      id      : [null, []] ,
    });
  }

  restablecerBD (): void {
    let comp = this;
    this.http.post('http://localhost/imparcial/src/api/restablecer', {})
      .subscribe({
        next : function (response) {
          comp.cargarPeliculas();
          comp.cargarGeneros();

        },
      });
  }

  private cargarGeneros (): void {
    let comp = this;
    this.http.get('http://localhost/imparcial/src/api/generos')
      .subscribe({
        next : function (response) {
          comp.generos = response;
        },
      });
  }

  private cargarPeliculas (): void {
    let comp = this;
    this.http.get('http://localhost/imparcial/src/api/peliculas')
      .subscribe({
        next : function (response) {
          comp.peliculas = response;
        },
      });
  }

  guardar (): void {
    let comp = this;
    if (this.miFormulario.value.id) {
      this.http.patch('http://localhost/imparcial/src/api/peliculas/' + this.miFormulario.value.id, this.miFormulario.value)
      .subscribe({
        next : function (response: any) {
          comp.cargarPeliculas();
        },
      });
    } else {
      this.http.post('http://localhost/imparcial/src/api/peliculas', this.miFormulario.value)
      .subscribe({
        next : function (response: any) {
          comp.cargarPeliculas();
        },
      });
    }
    this.descartar();
  }

  descartar (): void {
    this.miFormulario.setValue({
      titulo  : '',
      anio    : '',
      generos : [],
      id      : null,
    });
  }

  editar (pelicula: any): void {
    let comp = this;
    this.http.get('http://localhost/imparcial/src/api/peliculas/' + pelicula.id)
      .subscribe({
        next : function (response: any) {
          comp.miFormulario.setValue({
            titulo: response.titulo,
            anio: response.anio,
            generos: response.generos,
            id: pelicula.id,
          });
        },
      });
  }

  borrar (pelicula: any): void {
    if (confirm("¿Estás seguro de que querés borrar esta película?")) {
      let comp = this;
      this.http.delete('http://localhost/imparcial/src/api/peliculas/' + pelicula.id)
        .subscribe({
          next : function (response: any) {
            if(comp.miFormulario.value.id==pelicula.id) {
              comp.descartar();
            }
            comp.cargarPeliculas();
          },
        });
    }
  }

  ngOnInit () {
    let comp = this;
    this.cargarGeneros();
    this.cargarPeliculas();
  }
}
