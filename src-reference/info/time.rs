use chrono::Local;

pub fn get_time() -> i64 {
    let now = Local::now();

    now.timestamp_millis()
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_get_time_epoch_sanity() {
        let ms = get_time();
        assert!(ms > 1700000000000); // Sanity check for recent date
    }

    #[test]
    fn test_get_time_monotonicity() {
        let ms1 = get_time();
        std::thread::sleep(std::time::Duration::from_millis(1));
        let ms2 = get_time();
        assert!(ms2 >= ms1);
    }
}
