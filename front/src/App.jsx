import { useState, useEffect } from 'react'
import './App.css'

function App() {
  const [forecasts, setForecasts] = useState([]);
  const [error, setError] = useState(null);

  useEffect(() => {
    async function fetchWeather() {
      try{
        const response = await fetch('http://127.0.0.1:8000/api/get-weather?latitude=-12.97&longitude=-38.49&hourly=temperature_2m,relative_humidity_2m,precipitation,rain,cloud_cover,wind_speed_10m&timezone=America%2FSao_Paulo');
        
        if(!response.ok) {
          throw new Error('A resposta da rede não foi ok.');
        }

        const data = await response.json();

        const formattedData = data.hourly.time.map((time, index) => {
          return {
            id: index,
            time: time,
            temperature: data.hourly.temperature_2m[index],
            relativehumidity: data.hourly.relative_humidity_2m[index],
            precipitation: data.hourly.precipitation[index],
            rain: data.hourly.rain[index],
            cloudcover: data.hourly.cloud_cover[index],
            windspeed: data.hourly.wind_speed_10m[index]
          };
        });

        setForecasts(formattedData);
      } catch(e) {
        console.log('Erro ao buscar os dados: ', e);
        setError(e.message);
      }
    }

    fetchWeather();
  }, []);

  const formatDateTime = (isoString => {
    const date = new Date(isoString);
    return date.toLocaleString('pt-br', {day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit'});
  });

  return (
    <div className='App'>
      <h1>Previsão do tempo (Salvador)</h1>
      {error && <p style={{color: 'red'}}>Ocorreu um erro: {error}</p>}

      {forecasts.length === 0 && !error ? <p>Carregando...</p> : (
        <table style={{padding: 0}}>
          <thead>
            <th>Data e hora</th>
            <th>Temperatura</th>
            <th>Humidade Relativa</th>
            <th>Precipitação</th>
            <th>Chuva</th>
            <th>Nuvens</th>
            <th>Velocidade do vento</th>
          </thead>
          <tbody>
            {forecasts.map(item => (
              <tr key={item.id} style={{marginBottom: '10px', }}>
                <td>{formatDateTime(item.time)}</td>
                <td>{item.temperature}°C</td>
                <td>{item.relativehumidity}</td>
                <td>{item.precipitation}</td>
                <td>{item.rain}</td>
                <td>{item.cloudcover}</td>
                <td>{item.windspeed}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}

export default App
